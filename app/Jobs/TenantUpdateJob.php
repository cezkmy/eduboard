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
    ) {
    }

    public function handle(): void
    {
        $tenant = Tenant::find($this->tenantId);
        if (!$tenant)
            return;

        $base = base_path();
        $storagePath = storage_path("app/updates/tenant_{$this->tenantId}");
        $currentVersion = $tenant->system_version ?? config('app.version', 'v1.0.0');

        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
        }

        $tenant->setAttribute('is_updating', true);
        $tenant->setAttribute('updating_message', "Applying database updates for version {$this->targetVersion}...");
        $tenant->save();

        try {
            $this->log("Starting database-only update for tenant: {$tenant->id} to version {$this->targetVersion}", 'info');

            // 1. Backup Database
            $this->log('Creating database backup...', 'info');
            $dbBackupSql = $storagePath . "/db_before_{$this->targetVersion}_" . time() . ".sql";
            $this->backupDatabase($dbBackupSql);
            $tenant->latest_db_backup_path = $dbBackupSql;
            $this->log('Database backup created successfully.', 'success');

            // 2. Run Tenant Migrations
            // Note: In a shared codebase, the migration files are already on the disk from the Master System Update.
            // We just need to run them for this specific tenant.
            $this->log('Running tenant migrations and seeders...', 'info');
            $this->runTenantCommand('tenants:migrate', [
                '--tenants' => $tenant->id,
                '--force' => true,
                '--seed' => true,
            ]);

            // 3. Clear Caches for this tenant context
            $this->log('Clearing tenant-specific caches...', 'info');
            // Use Artisan::call instead of runProcess to avoid connection issues on some environments
            $this->runTenantCommand('cache:clear');
            $this->runTenantCommand('view:clear');

            // 4. Finalize Version Update
            $tenant->update([
                'previous_version' => $currentVersion,
                'system_version' => $this->targetVersion,
            ]);

            // Also update central database record to stay in sync
            \Illuminate\Support\Facades\DB::connection('mysql')->table('tenants')
                ->where('id', $tenant->getTenantKey())
                ->update(['system_version' => $this->targetVersion]);

            $this->log("Tenant database successfully updated to {$this->targetVersion}!", 'success');
        } catch (Exception $e) {
            $this->log("FATAL UPDATE ERROR: " . $e->getMessage() . " in " . basename($e->getFile()) . ":" . $e->getLine(), 'error');
            $this->log('Database update failed. The system remains online but this tenant might need manual intervention.', 'warning');
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

    protected function runProcess(array $command, $cwd)
    {
        $process = new Process($command, $cwd);
        $process->setTimeout(600);
        $process->run(function ($type, $buffer) {
            $logs = collect(explode("\n", rtrim($buffer)))->filter()->map(fn($l) => trim($l))->filter();
            foreach ($logs as $msg) {
                if (empty($msg) || $msg === '.' || $msg === '...')
                    continue;
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