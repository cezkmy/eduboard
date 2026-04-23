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

        // Prefer locally stored releases (from webhook) so tenants can browse/select versions.
        $releaseList = CentralSetting::getJson('github_releases', []);
        $sorted = collect($releaseList)
            ->filter(fn ($r) => !empty($r['tag_name']))
            ->sort(function ($a, $b) {
                return version_compare(ltrim($b['tag_name'], 'vV'), ltrim($a['tag_name'], 'vV'));
            })
            ->values();

        $release = $sorted->first() ?: GitHubService::getLatestRelease(true);
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

