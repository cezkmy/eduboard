<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\CentralSetting;
use App\Services\GitHubService;
use Illuminate\Http\Request;

class SystemUpdateController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('page_admin_settings')) {
            abort(403, 'Unauthorized.');
        }

        $tenant = tenant();
        $currentVersion = $tenant->system_version ?? config('app.version', 'v1.0.0');

        // Fetch real-time latest release from GitHub API to ensure timely detection
        $latestGithubRelease = GitHubService::getLatestRelease(true);

        // Get locally stored releases so tenants can browse/select versions
        $releaseList = CentralSetting::getJson('github_releases', []);
        $releaseCollection = collect($releaseList)->keyBy('tag_name');

        // Always inject the real-time latest release to the list
        if ($latestGithubRelease && !empty($latestGithubRelease['tag_name'])) {
            $releaseCollection->put($latestGithubRelease['tag_name'], $latestGithubRelease);

            // Optionally sync it back to central settings so it's globally updated
            if (!collect($releaseList)->contains('tag_name', $latestGithubRelease['tag_name'])) {
                CentralSetting::setJson('github_releases', $releaseCollection->values()->all());
                // Update system version for central admin tracking
                CentralSetting::set('system_version', $latestGithubRelease['tag_name']);
            }
        }

        $sorted = $releaseCollection
            ->filter(fn ($r) => !empty($r['tag_name']))
            ->sort(function ($a, $b) {
                return version_compare(ltrim($b['tag_name'], 'vV'), ltrim($a['tag_name'], 'vV'));
            })
            ->values();

        // Use the real-time release if available, otherwise fallback to the highest sorted locally
        $release = $latestGithubRelease ?: $sorted->first();
        $latestVersion = $release['tag_name'] ?? $currentVersion;

        $hasUpdate = version_compare(ltrim($latestVersion, 'vV'), ltrim($currentVersion, 'vV'), '>');
        $rollbackAvailable = !empty($tenant->previous_version);

        return view('tenant_ui.admin.system-update', compact(
            'tenant',
            'currentVersion',
            'latestVersion',
            'hasUpdate',
            'release',
            'sorted',
            'rollbackAvailable'
        ));
    }

    public function toggleAutoUpdate(Request $request)
    {
        if (!auth()->user()->hasPermission('page_admin_settings')) {
            abort(403, 'Unauthorized.');
        }

        $tenant = tenant();
        $enabled = $request->boolean('enabled');

        $tenant->setAttribute('auto_update_enabled', $enabled);
        $tenant->save();

        return response()->json(['success' => true, 'enabled' => $enabled]);
    }
}

