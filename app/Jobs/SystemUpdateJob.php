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

        if (!File::exists($storageUpdater)) {
            File::makeDirectory($storageUpdater, 0755, true);
        }

        try {
            $this->log("Running System Update to {$this->version}", 'info');

            $this->log('Putting application into maintenance mode...', 'info');
            $this->runProcess([PHP_BINARY, 'artisan', 'down'], $base);

            $this->log('Creating system backup before update...', 'info');
            $this->createBackup($base, $backupZip);
            CentralSetting::set('latest_stable_backup', $backupZip);
            CentralSetting::set('latest_stable_version', $currentVersion);
            $this->log("Backup created successfully at {$backupZip}", 'success');

            $this->log('Fetching latest Git tags...', 'info');
            $this->runProcess(['git', 'fetch', '--all'], $base);

            $this->log("Checking out release tag {$this->version}...", 'info');
            $this->runProcess(['git', 'checkout', 'tags/' . $this->version], $base);

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
            $this->log("FATAL UPDATE ERROR: " . $e->getMessage(), 'error');
            $this->log('Initiating rollback to previous stable version...', 'warning');

            try {
                $this->rollback($currentVersion, $base);
                $this->log('Rollback completed successfully. System restored to previous state.', 'info');
            } catch (Exception $rollbackException) {
                $this->log('CRITICAL: Rollback failed! ' . $rollbackException->getMessage(), 'error');
            }
        }
    }

    protected function rollback($previousVersion, $basePath)
    {
        if (empty($previousVersion)) {
            throw new Exception('No previous version available for rollback.');
        }

        $this->log('Putting application into maintenance mode for rollback...', 'info');
        $this->runProcess([PHP_BINARY, 'artisan', 'down'], $basePath);

        $this->log('Fetching latest Git tags for rollback...', 'info');
        $this->runProcess(['git', 'fetch', '--all'], $basePath);

        $this->log("Checking out rollback tag {$previousVersion}...", 'info');
        $this->runProcess(['git', 'checkout', 'tags/' . $previousVersion], $basePath);

        $this->log('Reinstalling Composer dependencies for rollback...', 'info');
        $this->runProcess(['composer', 'install'], $basePath);

        $this->log('Installing NPM packages for rollback...', 'info');
        $this->runProcess(DIRECTORY_SEPARATOR === '\\' ? ['cmd', '/c', 'npm', 'install'] : ['npm', 'install'], $basePath);
        $this->log('Building frontend assets for rollback...', 'info');
        $this->runProcess(DIRECTORY_SEPARATOR === '\\' ? ['cmd', '/c', 'npm', 'run', 'build'] : ['npm', 'run', 'build'], $basePath);

        $this->log('Rolling back database changes by one step...', 'info');
        $this->runProcess([PHP_BINARY, 'artisan', 'migrate:rollback', '--step=1'], $basePath);

        $this->log('Re-running database seeder after rollback...', 'info');
        $this->runProcess([PHP_BINARY, 'artisan', 'db:seed', '--force'], $basePath);

        $this->log('Clearing caches after rollback...', 'info');
        $this->runProcess([PHP_BINARY, 'artisan', 'cache:clear'], $basePath);
        $this->runProcess([PHP_BINARY, 'artisan', 'config:clear'], $basePath);
        $this->runProcess([PHP_BINARY, 'artisan', 'route:clear'], $basePath);
        $this->runProcess([PHP_BINARY, 'artisan', 'view:clear'], $basePath);

        $this->log('Bringing application back online after rollback...', 'info');
        $this->runProcess([PHP_BINARY, 'artisan', 'up'], $basePath);

        CentralSetting::set('system_version', $previousVersion);
        CentralSetting::set('last_notified_version', $previousVersion);
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
        if ($zip->open($zipPath) === true) {
            $zip->extractTo($extractDir);
            $zip->close();
        } else {
            throw new Exception("Failed to extract the downloaded release zip.");
        }
    }

    protected function overwriteFiles($extractDir, $basePath)
    {
        $directories = File::directories($extractDir);
        // GitHub release zips wrap contents in a root folder (e.g. cezkmy-eduboard-ab12cd)
        $wrapper = count($directories) == 1 ? $directories[0] : $extractDir;

        // Ensure we don't overwrite certain critical files if they exist
        $preserve = ['.env', 'storage', 'public/storage'];
        
        $files = File::allFiles($wrapper, true);
        foreach ($files as $file) {
            $relativePath = str_replace($wrapper . DIRECTORY_SEPARATOR, '', $file->getRealPath());
            
            // Skip preserved files
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
        $process->setTimeout(300); // 5 minutes

        // Stream output to logs
        $process->run(function ($type, $buffer) {
            $logs = collect(explode("\n", rtrim($buffer)))->filter()->map(function($line) {
                return trim($line);
            })->filter()->values();
            
            foreach($logs as $msg) {
                $msgType = $type === Process::ERR ? 'warning' : 'info';
                $this->log($msg, $msgType);
            }
        });

        if (!$process->isSuccessful()) {
            throw new Exception("Process failed: " . implode(' ', $command) . "\n" . $process->getErrorOutput());
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
