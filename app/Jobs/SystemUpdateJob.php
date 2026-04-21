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
        
        // Normalize version for file naming (remove 'v' prefix)
        $cleanVersion = ltrim($this->version, 'vV');
        
        $backupZip = $storageUpdater . '/backup_before_' . $cleanVersion . '_' . time() . '.zip';
        $downloadZip = $storageUpdater . '/release_' . $cleanVersion . '.zip';
        $extractDir = $storageUpdater . '/extracted_' . $cleanVersion;

        if (!File::exists($storageUpdater)) {
            File::makeDirectory($storageUpdater, 0755, true);
        }

        try {
            $this->log("Running System Update to {$this->version}", 'info');

            // 1. Create Backup
            $this->log("Creating system backup before update...");
            $this->createBackup($base, $backupZip);
            
            // Save this backup path as the latest stable rollback point
            \App\Models\CentralSetting::set('latest_stable_backup', $backupZip);
            \App\Models\CentralSetting::set('latest_stable_version', config('app.version', 'v1.0.0'));
            
            $this->log("Backup created successfully at {$backupZip}", 'success');

            // 2. Download Release
            $this->log("Downloading release from GitHub...");
            $this->downloadRelease($this->downloadUrl, $downloadZip);
            $this->log("Download complete.", 'success');

            // 3. Extract Release
            $this->log("Extracting release files...");
            $this->extractRelease($downloadZip, $extractDir);
            
            // 4. Copy over files
            $this->log("Overwriting system files with new version...", 'info');
            $this->overwriteFiles($extractDir, $base);
            $this->log("System files updated successfully.", 'success');

            // 5. Run Composer
            $this->log("Running Composer install (this may take a while)...", 'info');
            $this->runProcess(['composer', 'install', '--no-dev', '--optimize-autoloader'], $base);
            $this->log("Composer dependencies updated.", 'success');

            // 6. Run NPM (Optional if NPM is available)
            $this->log("Attempting NPM install and build...", 'info');
            try {
                $this->runProcess(DIRECTORY_SEPARATOR === '\\' ? ['cmd', '/c', 'npm', 'install'] : ['npm', 'install'], $base);
                $this->runProcess(DIRECTORY_SEPARATOR === '\\' ? ['cmd', '/c', 'npm', 'run', 'build'] : ['npm', 'run', 'build'], $base);
                $this->log("NPM build successful.", 'success');
            } catch (Exception $e) {
                $this->log("NPM build skipped or failed (Optional step): " . rtrim($e->getMessage()), 'warning');
            }

            // 7. Run Migrations
            $this->log("Running system migrations...", 'info');
            $this->runProcess(['php', 'artisan', 'migrate', '--force'], $base);
            
            $this->log("Running tenant migrations...", 'info');
            $this->runProcess(['php', 'artisan', 'tenants:migrate', '--force'], $base);
            $this->log("Migrations completed.", 'success');

            // 8. Optimize Cache
            $this->log("Clearing and optimizing caches...", 'info');
            $this->runProcess(['php', 'artisan', 'optimize:clear'], $base);
            
            $this->log("Update to {$this->version} has been completed successfully!", 'success');

            // Cleanup
            File::deleteDirectory($extractDir);
            File::delete($downloadZip);

        } catch (Exception $e) {
            $this->log("FATAL UPDATE ERROR: " . $e->getMessage(), 'error');
            $this->log("Initiating rollback from backup...", 'warning');
            
            try {
                $this->rollback($backupZip, $base);
                $this->log("Rollback completed successfully. System restored to previous state.", 'info');
            } catch (Exception $rollbackException) {
                $this->log("CRITICAL: Rollback failed! " . $rollbackException->getMessage(), 'error');
            }
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

    protected function rollback($backupZip, $basePath)
    {
        if (File::exists($backupZip)) {
            $zip = new ZipArchive;
            if ($zip->open($backupZip) === true) {
                $zip->extractTo($basePath);
                $zip->close();
            } else {
                throw new Exception("Failed to open backup zip for rollback.");
            }
        } else {
            throw new Exception("No backup file found to rollback from.");
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
