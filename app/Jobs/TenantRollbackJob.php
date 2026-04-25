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
use Symfony\Component\Process\Process;
use ZipArchive;
use Exception;

class TenantRollbackJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;

    public function __construct(
        public string $updateId,
        public string $tenantId
    ) {}

    public function handle(): void
    {
        $tenant = Tenant::find($this->tenantId);
        if (!$tenant) return;

        $base = base_path();
        $backupPath = $tenant->latest_backup_path;
        $previousVersion = $tenant->previous_version;

        if (!$backupPath || !File::exists($backupPath)) {
            $this->log('Critical Error: No backup file found for rollback.', 'error');
            return;
        }

        $tenant->setAttribute('is_updating', true);
        $tenant->setAttribute('updating_message', "Rolling back to {$previousVersion}...");
        $tenant->save();

        try {
            $this->log("Initiating rollback for tenant: {$tenant->id} to {$previousVersion}", 'info');

            $this->log('Putting system into maintenance mode...', 'info');
            $this->runProcess([PHP_BINARY, 'artisan', 'down'], $base);

            // 1. Restore Database
            $dbBackupPath = $tenant->latest_db_backup_path;
            if ($dbBackupPath && File::exists($dbBackupPath)) {
                $this->log('Restoring database from backup...', 'info');
                $this->restoreDatabase($dbBackupPath);
                $this->log('Database restored successfully.', 'success');
            }

            // 2. Restore Files
            $this->log('Restoring files from backup archive...', 'info');
            $zip = new ZipArchive;
            if ($zip->open($backupPath) === true) {
                $zip->extractTo($base);
                $zip->close();
                $this->log('Files restored successfully.', 'success');
            } else {
                throw new Exception('Failed to open backup zip.');
            }

            $this->log('Reinstalling dependencies...', 'info');
            $this->runProcess(['composer', 'install'], $base);
            $this->runProcess(DIRECTORY_SEPARATOR === '\\' ? ['cmd', '/c', 'npm', 'install'] : ['npm', 'install'], $base);
            $this->runProcess(DIRECTORY_SEPARATOR === '\\' ? ['cmd', '/c', 'npm', 'run', 'build'] : ['npm', 'run', 'build'], $base);

            $this->log('Rolling back database changes...', 'info');
            Artisan::call('tenants:migrate', [
                '--tenants' => $tenant->id,
                '--force' => true,
            ]);

            $this->log('Clearing caches...', 'info');
            $this->runProcess([PHP_BINARY, 'artisan', 'cache:clear'], $base);
            $this->runProcess([PHP_BINARY, 'artisan', 'config:clear'], $base);

            $this->log('Bringing system back online...', 'info');
            $this->runProcess([PHP_BINARY, 'artisan', 'up'], $base);

            $tenant->update([
                'system_version' => $previousVersion,
                'previous_version' => null,
            ]);

            $this->log("Rollback to {$previousVersion} completed successfully!", 'success');
        } catch (Exception $e) {
            $this->log("ROLLBACK ERROR: " . $e->getMessage(), 'error');
            $this->runProcess([PHP_BINARY, 'artisan', 'up'], $base);
        } finally {
            $tenant->setAttribute('is_updating', false);
            $tenant->setAttribute('updating_message', null);
            $tenant->save();
        }
    }

    protected function runProcess(array $command, $cwd)
    {
        $process = new Process($command, $cwd);
        $process->setTimeout(600);
        $process->run(function ($type, $buffer) {
            $logs = collect(explode("\n", rtrim($buffer)))->filter()->map(fn($l) => trim($l))->filter();
            foreach($logs as $msg) {
                $this->log($msg, $type === Process::ERR ? 'warning' : 'info');
            }
        });
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

    protected function log($message, $type = 'info')
    {
        UpdateLog::create([
            'update_id' => $this->updateId,
            'type' => $type,
            'message' => $message
        ]);
    }
}
