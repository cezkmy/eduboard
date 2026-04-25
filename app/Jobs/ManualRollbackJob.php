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

        try {
            $this->log('Manual Rollback Initiated', 'info');

            if (empty($this->rollbackVersion)) {
                throw new Exception('No rollback version available.');
            }

            $this->log('Putting application into maintenance mode for rollback...', 'info');
            $this->runProcess([PHP_BINARY, 'artisan', 'down'], $base);

            $this->log('Fetching latest Git tags for rollback...', 'info');
            $this->runProcess(['git', 'fetch', '--all'], $base);

            $this->log("Checking out rollback tag {$this->rollbackVersion}...", 'info');
            $this->runProcess(['git', 'checkout', 'tags/' . $this->rollbackVersion], $base);

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
        }
    }

    protected function runProcess(array $command, $cwd)
    {
        $process = new Process($command, $cwd);
        $process->setTimeout(300);

        $process->run(function ($type, $buffer) {
            $lines = collect(explode("\n", rtrim($buffer)))->filter()->map('trim');
            foreach ($lines as $line) {
                $this->log($line, $type === Process::ERR ? 'warning' : 'info');
            }
        });

        if (!$process->isSuccessful()) {
            throw new Exception('Process failed: ' . implode(' ', $command) . '\n' . $process->getErrorOutput());
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
