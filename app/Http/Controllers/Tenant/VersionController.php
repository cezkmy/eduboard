<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GitHubService;
use Symfony\Component\Process\Process;

class VersionController extends Controller
{
    protected function ensurePermission(): void
    {
        if (!auth()->user()->hasPermission('page_admin_settings')) {
            abort(403, 'Unauthorized.');
        }
    }

    protected function markTenantUpdating(\App\Models\Tenant $tenant, bool $state): void
    {
        $tenant->setAttribute('is_updating', $state);
        $tenant->setAttribute('updating_message', $state ? 'System update in progress for this tenant.' : null);
        $tenant->save();
    }

    protected function runTenantCommand(array $command): void
    {
        $process = new Process($command, base_path());
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(trim($process->getErrorOutput()) ?: 'Command execution failed.');
        }
    }

    /**
     * Apply the latest GitHub release version to the current tenant.
     */
    public function applyUpdate(Request $request)
    {
        $this->ensurePermission();

        $tenant = tenant();
        $requested = $request->input('version');
        $release = GitHubService::getLatestRelease(true);
        $latestVersion = $requested ?: ($release['tag_name'] ?? null);

        if (!$latestVersion) {
            return back()->with('error', 'Unable to fetch the latest release right now. Please try again.');
        }

        if ($tenant->system_version === $latestVersion) {
            return back()->with('info', 'Your system is already running the latest version.');
        }

        try {
            $this->markTenantUpdating($tenant, true);

            // Run tenant-specific migrations only for this tenant.
            $this->runTenantCommand([
                'php',
                'artisan',
                'tenants:migrate',
                '--tenants=' . $tenant->id,
                '--force',
            ]);

            $tenant->update([
                'previous_version' => $tenant->system_version,
                'system_version' => $latestVersion,
            ]);
        } catch (\Throwable $e) {
            return back()->with('error', 'Tenant update failed: ' . $e->getMessage());
        } finally {
            $this->markTenantUpdating($tenant, false);
        }

        return back()->with('success', "Tenant updated successfully to {$latestVersion}.");
    }

    /**
     * Rollback to the previous version.
     */
    public function rollback(Request $request)
    {
        $this->ensurePermission();

        $tenant = tenant();

        if (!$tenant->previous_version) {
            return back()->with('error', 'No previous version found to rollback to.');
        }

        $oldVersion = $tenant->previous_version;
        $currentVersion = $tenant->system_version;

        try {
            $this->markTenantUpdating($tenant, true);

            // Keep schema aligned after revert action for this tenant.
            $this->runTenantCommand([
                'php',
                'artisan',
                'tenants:migrate',
                '--tenants=' . $tenant->id,
                '--force',
            ]);

            $tenant->update([
                'system_version' => $oldVersion,
                'previous_version' => $currentVersion,
            ]);
        } catch (\Throwable $e) {
            return back()->with('error', 'Tenant rollback failed: ' . $e->getMessage());
        } finally {
            $this->markTenantUpdating($tenant, false);
        }

        return back()->with('success', "Tenant reverted from {$currentVersion} to {$oldVersion}.");
    }
}
