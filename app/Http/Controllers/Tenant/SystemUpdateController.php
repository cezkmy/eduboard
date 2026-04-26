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

        // Fetch real-time releases from GitHub API
        $allReleases = GitHubService::getAllReleases(false);
        $latestGithubRelease = !empty($allReleases) ? $allReleases[0] : null;

        // Get locally stored releases for historical browsing if API fails
        $releaseList = CentralSetting::getJson('github_releases', []);
        $releaseCollection = collect($releaseList)->keyBy('tag_name');

        // Sync real-time releases to the collection
        foreach ($allReleases as $r) {
            $releaseCollection->put($r['tag_name'], $r);
        }

        $sorted = $releaseCollection
            ->filter(fn ($r) => !empty($r['tag_name']))
            ->sort(function ($a, $b) {
                return version_compare(ltrim($b['tag_name'], 'vV'), ltrim($a['tag_name'], 'vV'));
            })
            ->values();

        $release = $latestGithubRelease ?: $sorted->first();
        $latestVersion = $release['tag_name'] ?? $currentVersion;

        $hasUpdate = version_compare(ltrim($latestVersion, 'vV'), ltrim($currentVersion, 'vV'), '>');
        
        $rollbackAvailable = false;
        if (!empty($tenant->previous_version) && !empty($tenant->latest_backup_path)) {
            if (\Illuminate\Support\Facades\File::exists($tenant->latest_backup_path)) {
                $rollbackAvailable = true;
            }
        }

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

