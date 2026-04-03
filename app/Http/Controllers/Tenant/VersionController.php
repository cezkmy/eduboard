<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GitHubService;
use Illuminate\Support\Facades\DB;

class VersionController extends Controller
{
    /**
     * Apply the latest GitHub release version to the current tenant.
     */
    public function applyUpdate(Request $request)
    {
        $tenant = tenant();
        $latestVersion = GitHubService::getLatestVersion();

        if ($tenant->system_version === $latestVersion) {
            return back()->with('info', 'Your system is already running the latest version.');
        }

        // Store current as previous for rollback
        $tenant->update([
            'previous_version' => $tenant->system_version,
            'system_version' => $latestVersion
        ]);

        return back()->with('success', "Successfully updated to {$latestVersion}!");
    }

    /**
     * Rollback to the previous version.
     */
    public function rollback(Request $request)
    {
        $tenant = tenant();

        if (!$tenant->previous_version) {
            return back()->with('error', 'No previous version found to rollback to.');
        }

        $oldVersion = $tenant->previous_version;
        $currentVersion = $tenant->system_version;

        $tenant->update([
            'system_version' => $oldVersion,
            'previous_version' => null // Clear rollback after one use
        ]);

        return back()->with('success', "Successfully rolled back from {$currentVersion} to {$oldVersion}.");
    }
}
