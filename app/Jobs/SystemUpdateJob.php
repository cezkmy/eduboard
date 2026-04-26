<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;
use App\Models\CentralSetting;
use App\Models\UpdateLog;
use ZipArchive;
use Exception;

class SystemUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes max
    public $updateId;
    public $downloadUrl;
    public $version;

    /**
     * Create a new job instance.
     */
    public function __construct($updateId, $downloadUrl, $version)
    {
        $this->updateId = $updateId;
        $this->downloadUrl = $downloadUrl;
        $this->version = $version;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $base = base_path();
        $storageUpdater = storage_path('app/updater');
        $currentVersion = CentralSetting::get('system_version', config('app.version', 'v1.0.0'));
        $cleanVersion = ltrim($this->version, 'vV');
        $backupZip = $storageUpdater . '/backup_before_' . $cleanVersion . '_' . time() . '.zip';
        $dbBackupSql = $storageUpdater . '/db_before_' . $cleanVersion . '_' . time() . '.sql';

        if (!File::exists($storageUpdater)) {
            File::makeDirectory($storageUpdater, 0755, true);
        }

        // Set lock
        CentralSetting::set('is_system_updating', true);

        try {
            $this->log("Running System Update to {$this->version}", 'info');

            $this->log('Putting application into maintenance mode...', 'info');
            $this->runProcess([PHP_BINARY, 'artisan', 'down'], $base);

            $this->log('Creating database backup...', 'info');
            $this->backupDatabase($dbBackupSql);
            $this->log('Database backup created successfully.', 'success');

            $this->log('Creating system files backup...', 'info');
            $this->createBackup($base, $backupZip);
            CentralSetting::set('latest_stable_backup', $backupZip);
            CentralSetting::set('latest_stable_db_backup', $dbBackupSql);
            CentralSetting::set('latest_stable_version', $currentVersion);
            $this->log("Backup created successfully at {$backupZip}", 'success');

            $this->log('Downloading release archive from GitHub...', 'info');
            $zipPath = $storageUpdater . DIRECTORY_SEPARATOR . 'release.zip';
            $this->downloadRelease($this->downloadUrl, $zipPath);

            $this->log('Extracting release archive...', 'info');
            $extractDir = $storageUpdater . DIRECTORY_SEPARATOR . 'extract';
            if (File::exists($extractDir)) {
                File::deleteDirectory($extractDir);
            }
            $this->extractRelease($zipPath, $extractDir);

            $this->log('Applying new files to system core...', 'info');
            $this->overwriteFiles($extractDir, $base);
            
            // Clean up
            File::delete($zipPath);
            File::deleteDirectory($extractDir);

            $this->log('Installing Composer dependencies...', 'info');
            $this->runProcess(['composer', 'install', '--no-dev', '--optimize-autoloader'], $base);
            $this->log('Composer install finished.', 'success');

            $this->log('Installing NPM packages...', 'info');
            $this->runProcess(DIRECTORY_SEPARATOR === '\\' ? ['cmd', '/c', 'npm', 'install'] : ['npm', 'install'], $base);
            $this->log('Building frontend assets...', 'info');
            $this->runProcess(DIRECTORY_SEPARATOR === '\\' ? ['cmd', '/c', 'npm', 'run', 'build'] : ['npm', 'run', 'build'], $base);
            $this->log('NPM build finished.', 'success');

            $this->log('Running migrations and seeders...', 'info');
            $this->runProcess([PHP_BINARY, 'artisan', 'migrate', '--seed', '--force'], $base);

            $this->log('Clearing caches...', 'info');
            $this->runProcess([PHP_BINARY, 'artisan', 'config:clear'], $base);
            $this->runProcess([PHP_BINARY, 'artisan', 'cache:clear'], $base);
            $this->runProcess([PHP_BINARY, 'artisan', 'view:clear'], $base);
            $this->runProcess([PHP_BINARY, 'artisan', 'route:clear'], $base);

            $this->log('Rebuilding caches...', 'info');
            $this->runProcess([PHP_BINARY, 'artisan', 'config:cache'], $base);
            $this->runProcess([PHP_BINARY, 'artisan', 'route:cache'], $base);
            $this->runProcess([PHP_BINARY, 'artisan', 'view:cache'], $base);

            $this->log('Bringing application back online...', 'info');
            $this->runProcess([PHP_BINARY, 'artisan', 'up'], $base);

            CentralSetting::set('system_version', $this->version);
            CentralSetting::set('last_notified_version', $this->version);

            $this->log("Update to {$this->version} has been completed successfully!", 'success');
        } catch (Exception $e) {
            $this->log("FATAL UPDATE ERROR: " . $e->getMessage() . " in " . basename($e->getFile()) . ":" . $e->getLine(), 'error');
            $this->log('Initiating rollback to previous stable version...', 'warning');

            try {
                $this->rollback($currentVersion, $base);
                $this->log('Rollback completed successfully. System restored to previous state.', 'info');
            } catch (Exception $rollbackException) {
                $this->log('CRITICAL: Rollback failed! ' . $rollbackException->getMessage(), 'error');
            }
        } finally {
            CentralSetting::set('is_system_updating', false);
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
                // Fallback: If mysqldump is not available, we skip DB backup but log it
                $this->log('Warning: mysqldump not found or failed. Database backup skipped.', 'warning');
            }
        } elseif ($connection === 'sqlite') {
            File::copy($config['database'], $destination);
        }
    }

    protected function rollback($previousVersion, $basePath)
    {
        if (empty($previousVersion)) {
            throw new Exception('No previous version available for rollback.');
        }

        $this->log('Initiating automatic rollback...', 'warning');

        $this->log('Putting application into maintenance mode for rollback...', 'info');
        $this->runProcess([PHP_BINARY, 'artisan', 'down'], $basePath);

        $backupZip = CentralSetting::get('latest_stable_backup');
        $dbBackupSql = CentralSetting::get('latest_stable_db_backup');

        if ($dbBackupSql && File::exists($dbBackupSql)) {
            $this->log('Restoring database from stable backup...', 'info');
            // We use the same restore logic as ManualRollbackJob but inside this job
            $this->restoreDatabase($dbBackupSql);
            $this->log('Database restored successfully.', 'success');
        }

        if ($backupZip && File::exists($backupZip)) {
            $this->log('Restoring files from stable backup archive...', 'info');
            $zip = new ZipArchive;
            if ($zip->open($backupZip) === true) {
                $zip->extractTo($basePath);
                $zip->close();
                $this->log('Files restored successfully.', 'success');
            } else {
                throw new Exception('Failed to open backup zip for rollback.');
            }
        } else {
            $this->log('Backup not found, attempting Git rollback...', 'warning');
            $this->runProcess(['git', 'fetch', '--all'], $basePath);
            $this->runProcess(['git', 'checkout', 'tags/' . $previousVersion], $basePath);
        }

        $this->log('Re-running dependency check...', 'info');
        $this->runProcess(['composer', 'install'], $basePath);

        $this->log('Clearing caches after rollback...', 'info');
        $this->runProcess([PHP_BINARY, 'artisan', 'cache:clear'], $basePath);
        $this->runProcess([PHP_BINARY, 'artisan', 'config:clear'], $basePath);

        $this->log('Bringing application back online after rollback...', 'info');
        $this->runProcess([PHP_BINARY, 'artisan', 'up'], $basePath);

        CentralSetting::set('system_version', $previousVersion);
        CentralSetting::set('last_notified_version', $previousVersion);
    }

    protected function restoreDatabase($source)
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if ($connection === 'mysql') {
            $command = [
                'mysql',
                '--user=' . $config['username'],
                '--password=' . $config['password'],
                '--host=' . $config['host'],
                '--port=' . $config['port'],
                $config['database'],
            ];
            
            $process = new Process($command);
            $process->setInput(File::get($source));
            $process->run();
        } elseif ($connection === 'sqlite') {
            File::copy($source, $config['database']);
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
        $headers = [];
        if ($token = config('services.github.token')) {
            $headers['Authorization'] = "token {$token}";
        }

        $response = Http::withHeaders($headers)
            ->withOptions(['stream' => true])
            ->get($url);

        if ($response->failed()) {
            throw new Exception("Download failed with HTTP status: " . $response->status() . " (URL: " . $url . ")");
        }

        $body = $response->body();
        if (empty($body)) {
            throw new Exception("Downloaded release zip is empty.");
        }

        file_put_contents($destination, $body);
    }

    protected function extractRelease($zipPath, $extractDir)
    {
        if (!File::exists($extractDir)) {
            File::makeDirectory($extractDir, 0755, true);
        }
        
        $zip = new ZipArchive;
        $res = $zip->open($zipPath);
        
        if ($res === true) {
            $zip->extractTo($extractDir);
            $zip->close();
        } else {
            // FALLBACK: Use PowerShell on Windows if ZipArchive fails
            if (str_starts_with(PHP_OS, 'WIN')) {
                $this->log("[WARNING] ZipArchive failed (Code {$res}). Trying PowerShell Expand-Archive...", 'warning');
                $psCommand = "powershell -Command \"Expand-Archive -Force -Path '{$zipPath}' -DestinationPath '{$extractDir}'\"";
                exec($psCommand, $output, $returnVar);
                
                if ($returnVar !== 0) {
                    throw new Exception("Failed to extract release zip via PHP and PowerShell. (Zip Path: {$zipPath})");
                }
            } else {
                throw new Exception("Failed to extract release zip. Error code: " . $res . " (Path: " . $zipPath . ")");
            }
        }
    }

    protected function overwriteFiles($extractDir, $basePath)
    {
        $directories = File::directories($extractDir);
        $wrapper = count($directories) == 1 ? $directories[0] : $extractDir;
        $preserve = [
            '.env', 
            'storage', 
            'public/storage', 
            'app/Jobs/SystemUpdateJob.php',
            'app/Jobs/TenantUpdateJob.php'
        ];
        
        $files = File::allFiles($wrapper, true);
        try {
            foreach ($files as $file) {
                // Get real path and ensure it is normalized
                $realFilePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file->getRealPath());
                
                // Calculate relative path more safely
                $wrapperNormalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $wrapper);
                $relativePath = str_replace($wrapperNormalized, '', $realFilePath);
                $relativePath = ltrim($relativePath, DIRECTORY_SEPARATOR);

                $isPreserved = false;
                foreach ($preserve as $p) {
                    if ($relativePath === $p || str_starts_with($relativePath, $p . DIRECTORY_SEPARATOR)) {
                        $isPreserved = true;
                        break;
                    }
                }
                
                if (!$isPreserved) {
                    // Build destination path
                    $normalizedBase = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $basePath);
                    $destPath = rtrim($normalizedBase, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $relativePath;
                    
                    $destDir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname($destPath));
                    $destDir = rtrim($destDir, DIRECTORY_SEPARATOR);

                    if (!file_exists($destDir)) {
                        // Try PHP first
                        if (!@mkdir($destDir, 0755, true)) {
                            // FALLBACK: On Windows, use PowerShell if PHP fails
                            if (str_starts_with(PHP_OS, 'WIN')) {
                                $psCommand = "powershell -Command \"New-Item -ItemType Directory -Force -Path '{$destDir}'\"";
                                exec($psCommand, $output, $returnVar);
                                
                                if ($returnVar !== 0) {
                                    throw new Exception("Failed to create directory [{$destDir}] via PHP and PowerShell.");
                                }
                            } else {
                                throw new Exception("Failed to create directory [{$destDir}]");
                            }
                        }
                    }

                    File::copy($file->getRealPath(), $destPath);
                }
            }
        } catch (\Exception $e) {
            throw new Exception("File Update Error at [{$relativePath}]: " . $e->getMessage() . " (Line " . $e->getLine() . ")");
        }
    }

    protected function runProcess(array $command, $cwd)
    {
        $process = new Process($command, $cwd);
        $process->setTimeout(600); // 10 minutes per process

        // Stream output to logs
        $process->run(function ($type, $buffer) {
            $logs = collect(explode("\n", rtrim($buffer)))->filter()->map(function($line) {
                return trim($line);
            })->filter()->values();
            
            foreach($logs as $msg) {
                // Ignore empty or trivial lines
                if (empty($msg) || $msg === '.' || $msg === '...') continue;
                
                $msgType = $type === Process::ERR ? 'warning' : 'info';
                $this->log($msg, $msgType);
            }
        });

        if (!$process->isSuccessful()) {
            $errorOutput = $process->getErrorOutput() ?: $process->getOutput();
            throw new Exception("Process failed: " . implode(' ', $command) . "\n" . $errorOutput);
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
