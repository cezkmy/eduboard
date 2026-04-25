<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Models\UpdateLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use ZipArchive;
use Exception;

class TenantUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 900; // 15 minutes

    public function __construct(
        public string $updateId,
        public string $tenantId,
        public string $targetVersion,
        public string $zipUrl
    ) {}

    public function handle(): void
    {
        $tenant = Tenant::find($this->tenantId);
        if (!$tenant) return;

        $base = base_path();
        $storagePath = storage_path("app/updates/tenant_{$this->tenantId}");
        $currentVersion = $tenant->system_version ?? config('app.version', 'v1.0.0');
        
        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
        }

        $tenant->setAttribute('is_updating', true);
        $tenant->setAttribute('updating_message', "Updating to {$this->targetVersion}...");
        $tenant->save();

        try {
            $this->log("Starting update for tenant: {$tenant->id} to version {$this->targetVersion}", 'info');

            // 1. Backup Database and Files
            $this->log('Creating database backup...', 'info');
            $dbBackupSql = $storagePath . "/db_before_{$this->targetVersion}_" . time() . ".sql";
            $this->backupDatabase($dbBackupSql);
            $tenant->latest_db_backup_path = $dbBackupSql;
            $this->log('Database backup created successfully.', 'success');

            $this->log('Creating backup of current system files...', 'info');
            $backupZip = $storagePath . "/backup_before_{$this->targetVersion}_" . time() . ".zip";
            $this->createBackup($base, $backupZip);
            $tenant->latest_backup_path = $backupZip;
            $tenant->save();
            $this->log('Files backup created successfully.', 'success');

            // 2. Download and Extract
            $this->log('Downloading release archive from GitHub...', 'info');
            $zipPath = $storagePath . '/release.zip';
            $this->downloadRelease($this->zipUrl, $zipPath);

            $this->log('Extracting release archive...', 'info');
            $extractDir = $storagePath . '/extract';
            if (File::exists($extractDir)) File::deleteDirectory($extractDir);
            $this->extractRelease($zipPath, $extractDir);

            $this->log('Applying new files to core application...', 'info');
            $this->overwriteFiles($extractDir, $base);
            
            File::delete($zipPath);
            File::deleteDirectory($extractDir);

            // 3. Run Commands
            $this->log('Installing Composer dependencies...', 'info');
            $this->runProcess(['composer', 'install', '--no-dev', '--optimize-autoloader'], $base);

            $this->log('Installing NPM packages...', 'info');
            $this->runProcess(DIRECTORY_SEPARATOR === '\\' ? ['cmd', '/c', 'npm', 'install'] : ['npm', 'install'], $base);
            
            $this->log('Building frontend assets...', 'info');
            $this->runProcess(DIRECTORY_SEPARATOR === '\\' ? ['cmd', '/c', 'npm', 'run', 'build'] : ['npm', 'run', 'build'], $base);

            $this->log('Running tenant migrations...', 'info');
            $this->runTenantCommand('tenants:migrate', [
                '--tenants' => $tenant->id,
                '--force' => true,
                '--seed' => true,
            ]);

            $this->log('Clearing caches...', 'info');
            $this->runProcess([PHP_BINARY, 'artisan', 'config:clear'], $base);
            $this->runProcess([PHP_BINARY, 'artisan', 'cache:clear'], $base);
            $this->runProcess([PHP_BINARY, 'artisan', 'view:clear'], $base);
            $this->runProcess([PHP_BINARY, 'artisan', 'route:clear'], $base);

            $tenant->update([
                'previous_version' => $currentVersion,
                'system_version' => $this->targetVersion,
            ]);

            $this->log("Tenant update to {$this->targetVersion} completed successfully!", 'success');
        } catch (Exception $e) {
            $this->log("FATAL UPDATE ERROR: " . $e->getMessage(), 'error');
            $this->log('Update failed. The system remains online but this tenant might be in an inconsistent state.', 'warning');
        } finally {
            $tenant->setAttribute('is_updating', false);
            $tenant->setAttribute('updating_message', null);
            $tenant->save();
        }
    }

    protected function runTenantCommand(string $command, array $parameters = []): void
    {
        $exitCode = Artisan::call($command, $parameters);
        if ($exitCode !== 0) {
            $output = trim(Artisan::output());
            throw new \RuntimeException("Artisan command failed: {$output}");
        }
    }

    protected function backupDatabase($destination)
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if ($connection === 'mysql') {
            $command = [
                'mysqldump',
                '--user=' . $config['username'],
                '--password=' . $config['password'],
                '--host=' . $config['host'],
                '--port=' . $config['port'],
                $config['database'],
            ];
            
            $process = new Process($command);
            $process->run();
            
            if ($process->isSuccessful()) {
                File::put($destination, $process->getOutput());
            } else {
                $this->log('Warning: mysqldump not found or failed. Database backup skipped.', 'warning');
            }
        } elseif ($connection === 'sqlite') {
            File::copy($config['database'], $destination);
        }
    }

    protected function createBackup($basePath, $destination)
    {
        $zip = new ZipArchive();
        if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($basePath),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($basePath) + 1);

                    // Skip large/unnecessary directories
                    if (str_starts_with($relativePath, 'vendor' . DIRECTORY_SEPARATOR) ||
                        str_starts_with($relativePath, 'node_modules' . DIRECTORY_SEPARATOR) ||
                        str_starts_with($relativePath, 'storage' . DIRECTORY_SEPARATOR) ||
                        str_starts_with($relativePath, '.git' . DIRECTORY_SEPARATOR)) {
                        continue;
                    }

                    $zip->addFile($filePath, $relativePath);
                }
            }
            $zip->close();
        } else {
            throw new Exception("Failed to create backup zip file.");
        }
    }

    protected function downloadRelease($url, $destination)
    {
        $ch = curl_init($url);
        $file = fopen($destination, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $file);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Laravel-App');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        fclose($file);
        curl_close($ch);

        if ($status >= 400) {
            throw new Exception("Download failed with HTTP status: {$status}");
        }
    }

    protected function extractRelease($zipPath, $extractDir)
    {
        if (!File::exists($extractDir)) File::makeDirectory($extractDir, 0755, true);
        $zip = new ZipArchive;
        if ($zip->open($zipPath) === true) {
            $zip->extractTo($extractDir);
            $zip->close();
        } else {
            throw new Exception("Failed to extract release zip.");
        }
    }

    protected function overwriteFiles($extractDir, $basePath)
    {
        $directories = File::directories($extractDir);
        $wrapper = count($directories) == 1 ? $directories[0] : $extractDir;
        $preserve = ['.env', 'storage', 'public/storage'];
        
        $files = File::allFiles($wrapper, true);
        foreach ($files as $file) {
            $relativePath = str_replace($wrapper . DIRECTORY_SEPARATOR, '', $file->getRealPath());
            $isPreserved = false;
            foreach ($preserve as $p) {
                if ($relativePath === $p || str_starts_with($relativePath, $p . DIRECTORY_SEPARATOR)) {
                    $isPreserved = true;
                    break;
                }
            }
            if (!$isPreserved) {
                $destPath = $basePath . DIRECTORY_SEPARATOR . $relativePath;
                File::ensureDirectoryExists(dirname($destPath));
                File::copy($file->getRealPath(), $destPath);
            }
        }
    }

    protected function runProcess(array $command, $cwd)
    {
        $process = new Process($command, $cwd);
        $process->setTimeout(600);
        $process->run(function ($type, $buffer) {
            $logs = collect(explode("\n", rtrim($buffer)))->filter()->map(fn($l) => trim($l))->filter();
            foreach($logs as $msg) {
                if (empty($msg) || $msg === '.' || $msg === '...') continue;
                $this->log($msg, $type === Process::ERR ? 'warning' : 'info');
            }
        });

        if (!$process->isSuccessful()) {
            throw new Exception("Process failed: " . implode(' ', $command));
        }
    }

    protected function log($message, $type = 'info')
    {
        UpdateLog::create([
            'update_id' => $this->updateId,
            'type' => $type,
            'message' => $message
        ]);
    }
}
