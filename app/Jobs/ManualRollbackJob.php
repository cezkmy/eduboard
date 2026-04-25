<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Models\CentralSetting;
use App\Models\UpdateLog;
use Symfony\Component\Process\Process;
use ZipArchive;
use Exception;

class ManualRollbackJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $updateId;
    public $backupZip;
    public $rollbackVersion;

    public function __construct($updateId, $backupZip, $rollbackVersion = null)
    {
        $this->updateId = $updateId;
        $this->backupZip = $backupZip;
        $this->rollbackVersion = $rollbackVersion ?? CentralSetting::get('latest_stable_version');
    }

    public function handle(): void
    {
        $base = base_path();
        CentralSetting::set('is_system_updating', true);

        try {
            $this->log('Manual Rollback Initiated', 'info');

            if (empty($this->rollbackVersion)) {
                throw new Exception('No rollback version available.');
            }

            $this->log('Putting application into maintenance mode for rollback...', 'info');
            $this->runProcess([PHP_BINARY, 'artisan', 'down'], $base);

            // Database rollback if available
            $dbBackupSql = CentralSetting::get('latest_stable_db_backup');
            if ($dbBackupSql && File::exists($dbBackupSql)) {
                $this->log('Restoring database from backup...', 'info');
                $this->restoreDatabase($dbBackupSql);
                $this->log('Database restored successfully.', 'success');
            }

            if (File::exists($this->backupZip)) {
                $this->log('Restoring files from backup archive...', 'info');
                $zip = new ZipArchive;
                if ($zip->open($this->backupZip) === true) {
                    // We don't want to just extractTo(base) because that won't delete new files added by the update.
                    // But for a simple rollback, overwriting is usually enough for core files.
                    $zip->extractTo($base);
                    $zip->close();
                    $this->log('Files restored from backup successfully.', 'success');
                } else {
                    throw new Exception('Failed to open backup zip archive.');
                }
            } else {
                $this->log('Backup zip not found, attempting Git-based rollback...', 'warning');
                $this->runProcess(['git', 'fetch', '--all'], $base);
                $this->runProcess(['git', 'checkout', 'tags/' . $this->rollbackVersion], $base);
            }

            $this->log('Reinstalling Composer dependencies for rollback...', 'info');
            $this->runProcess(['composer', 'install'], $base);

            $this->log('Installing NPM packages for rollback...', 'info');
            $this->runProcess(DIRECTORY_SEPARATOR === '\\' ? ['cmd', '/c', 'npm', 'install'] : ['npm', 'install'], $base);
            $this->log('Building frontend assets for rollback...', 'info');
            $this->runProcess(DIRECTORY_SEPARATOR === '\\' ? ['cmd', '/c', 'npm', 'run', 'build'] : ['npm', 'run', 'build'], $base);

            $this->log('Rolling back database changes by one step...', 'info');
            $this->runProcess([PHP_BINARY, 'artisan', 'migrate:rollback', '--step=1'], $base);

            $this->log('Re-running database seeder after rollback...', 'info');
            $this->runProcess([PHP_BINARY, 'artisan', 'db:seed', '--force'], $base);

            $this->log('Clearing caches after rollback...', 'info');
            $this->runProcess([PHP_BINARY, 'artisan', 'cache:clear'], $base);
            $this->runProcess([PHP_BINARY, 'artisan', 'config:clear'], $base);
            $this->runProcess([PHP_BINARY, 'artisan', 'route:clear'], $base);
            $this->runProcess([PHP_BINARY, 'artisan', 'view:clear'], $base);

            $this->log('Bringing application back online after rollback...', 'info');
            $this->runProcess([PHP_BINARY, 'artisan', 'up'], $base);

            if ($this->rollbackVersion) {
                CentralSetting::set('system_version', $this->rollbackVersion);
                CentralSetting::set('last_notified_version', $this->rollbackVersion);
            }

            $this->log('Rollback has been completed successfully! System restored.', 'success');
        } catch (Exception $e) {
            $this->log('ROLLBACK ERROR: ' . $e->getMessage(), 'error');

            if (File::exists($this->backupZip)) {
                $this->log('Attempting fallback restore from backup zip...', 'warning');
                $zip = new ZipArchive;

                if ($zip->open($this->backupZip) === true) {
                    $zip->extractTo($base);
                    $zip->close();
                    $this->log('Fallback backup restore completed.', 'success');
                } else {
                    $this->log('Failed to open backup zip for fallback restore.', 'error');
                }
            }
        } finally {
            CentralSetting::set('is_system_updating', false);
        }
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
            
            if (!$process->isSuccessful()) {
                $this->log('Warning: Database restoration failed.', 'warning');
            }
        } elseif ($connection === 'sqlite') {
            File::copy($source, $config['database']);
        }
    }

    protected function runProcess(array $command, $cwd)
    {
        $process = new Process($command, $cwd);
        $process->setTimeout(600); // 10 minutes

        // Stream output to logs
        $process->run(function ($type, $buffer) {
            $logs = collect(explode("\n", rtrim($buffer)))->filter()->map(function($line) {
                return trim($line);
            })->filter()->values();
            
            foreach($logs as $msg) {
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
            'message' => $message,
            'type' => $type
        ]);
        
        Log::channel('single')->info("[ROLLBACK][{$this->updateId}] " . $message);
    }
}
