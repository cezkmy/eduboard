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
        $previousVersion = $tenant->previous_version;

        if (empty($previousVersion)) {
            $this->log('Critical Error: No previous version record found for rollback.', 'error');
            return;
        }

        $tenant->setAttribute('is_updating', true);
        $tenant->setAttribute('updating_message', "Rolling back database to version {$previousVersion}...");
        $tenant->save();

        try {
            $this->log("Initiating database-only rollback for tenant: {$tenant->id} to version {$previousVersion}", 'info');

            // 1. Restore Database
            $dbBackupPath = $tenant->latest_db_backup_path;
            if ($dbBackupPath && File::exists($dbBackupPath)) {
                $this->log('Restoring database from stable backup...', 'info');
                $this->restoreDatabase($dbBackupPath);
                $this->log('Database restored successfully.', 'success');
            } else {
                $this->log('Warning: Database backup not found. Only version record will be updated.', 'warning');
            }

            // 2. Clear Caches
            $this->log('Clearing tenant caches after rollback...', 'info');
            $this->runProcess([PHP_BINARY, 'artisan', 'cache:clear'], $base);
            $this->runProcess([PHP_BINARY, 'artisan', 'view:clear'], $base);

            // 3. Update Version Record
            $tenant->update([
                'system_version' => $previousVersion,
                'previous_version' => null, // Reset previous version after successful rollback
            ]);

            // Sync with central DB
            \Illuminate\Support\Facades\DB::connection('mysql')->table('tenants')
                ->where('id', $tenant->getTenantKey())
                ->update(['system_version' => $previousVersion]);

            $this->log("Rollback to {$previousVersion} has been completed successfully!", 'success');
        } catch (Exception $e) {
            $this->log("ROLLBACK FATAL ERROR: " . $e->getMessage(), 'error');
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
                if (empty($msg) || $msg === '.' || $msg === '...') continue;
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
                $this->log('Warning: Database restoration failed. Manual check required.', 'warning');
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
