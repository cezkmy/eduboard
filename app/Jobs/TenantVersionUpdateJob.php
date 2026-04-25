<?php

namespace App\Jobs;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class TenantVersionUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;

    public function __construct(
        public string $tenantId,
        public string $targetVersion
    ) {}

    public function handle(): void
    {
        $tenant = Tenant::find($this->tenantId);
        if (!$tenant) return;

        $tenant->setAttribute('is_updating', true);
        $tenant->setAttribute('updating_message', "Auto-updating tenant to {$this->targetVersion}...");
        $tenant->save();

        try {
            // Run tenant-only migrations.
            $exitCode = Artisan::call('tenants:migrate', [
                '--tenants' => $tenant->id,
                '--force' => true,
            ]);

            if ($exitCode !== 0) {
                throw new \RuntimeException(trim(Artisan::output()) ?: 'Tenant migrate failed.');
            }

            $tenant->update([
                'previous_version' => $tenant->system_version,
                'system_version' => $this->targetVersion,
            ]);
        } finally {
            $tenant->setAttribute('is_updating', false);
            $tenant->setAttribute('updating_message', null);
            $tenant->save();
        }
    }
}

