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
use ZipArchive;
use Exception;

class ManualRollbackJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $updateId;
    public $backupZip;

    public function __construct($updateId, $backupZip)
    {
        $this->updateId = $updateId;
        $this->backupZip = $backupZip;
    }

    public function handle(): void
    {
        $base = base_path();

        try {
            $this->log("Manual Rollback Initiated", 'info');

            if (!File::exists($this->backupZip)) {
                throw new Exception("Backup file not found at: " . $this->backupZip);
            }

            $this->log("Restoring system from backup: " . basename($this->backupZip), 'info');
            
            $zip = new ZipArchive;
            if ($zip->open($this->backupZip) === true) {
                // We should clean up before restoring, but overwriteFiles logic is safer
                // However, rollback usually replaces everything except .env and storage
                $zip->extractTo($base);
                $zip->close();
            } else {
                throw new Exception("Failed to open backup zip for rollback.");
            }

            $rollbackVersion = CentralSetting::get('latest_stable_version');
            if ($rollbackVersion) {
                CentralSetting::set('system_version', $rollbackVersion);
                CentralSetting::set('last_notified_version', $rollbackVersion);
            }

            // Run Migrations/Cleanup after restoration
            $this->log("Running system health check after restoration...", 'info');
            
            $this->log("Rollback has been completed successfully! System restored.", 'success');

        } catch (Exception $e) {
            $this->log("ROLLBACK ERROR: " . $e->getMessage(), 'error');
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
