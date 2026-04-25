<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('eduboard:release {version}')]
#[Description('Send a system update email notification to all Tenant Admins.')]
class BroadcastSystemUpdate extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $version = $this->argument('version');
        $this->info("Preparing to broadcast release notes for {$version}...");

        $tenants = \App\Models\Tenant::where('status', 'Active')->get();
        if ($tenants->isEmpty()) {
            $this->warn('No active tenants found.');
            return 0;
        }

        $count = 0;
        tenancy()->runForMultiple($tenants, function ($tenant) use ($version, &$count) {
            $admin = \App\Models\User::where('role', 'admin')->orWhere('is_admin', true)->first();
            if ($admin) {
                try {
                    $admin->notify(new \App\Notifications\TenantSystemUpdateNotification($version));
                    $this->info("Notified admin of [{$tenant->school_name}]");
                    $count++;
                } catch (\Exception $e) {
                    $this->error("Failed to notify admin of [{$tenant->school_name}]: " . $e->getMessage());
                }
            }
        });

        $this->info("Successfully broadcasted update {$version} to {$count} tenant(s).");
        return 0;
    }
}
