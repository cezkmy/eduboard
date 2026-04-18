<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class UpdateTenantStorageUsage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eduboard:update-storage-usage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and update the total storage used by each tenant.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting storage usage calculation for all tenants...');

        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $this->line("Processing tenant: {$tenant->id}...");

            // Tenant storage is typically in storage/tenant<id>
            $tenantStoragePath = storage_path('tenant' . $tenant->id);

            if (!File::exists($tenantStoragePath)) {
                $this->warn("Storage path for tenant {$tenant->id} not found at: {$tenantStoragePath}");
                $tenant->update(['storage_used_gb' => 0]);
                continue;
            }

            // Calculate recursive directory size in bytes
            $totalSizeBytes = $this->getDirSize($tenantStoragePath);

            // Convert to Gigabytes (1 GB = 1024 * 1024 * 1024 bytes)
            $totalSizeGb = round($totalSizeBytes / (1024 * 1024 * 1024), 6);

            $tenant->update(['storage_used_gb' => $totalSizeGb]);

            $this->info("Updated storage for {$tenant->id}: {$totalSizeGb} GB (" . round($totalSizeBytes / (1024 * 1024), 2) . " MB)");
        }

        $this->info('All tenants storage usage updated successfully.');
    }

    /**
     * Get the size of a directory in bytes.
     *
     * @param string $directory
     * @return int
     */
    private function getDirSize($directory)
    {
        $size = 0;
        foreach (File::allFiles($directory) as $file) {
            $size += $file->getSize();
        }
        return $size;
    }
}
