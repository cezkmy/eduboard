<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Jobs\ManualRollbackJob;
use Illuminate\Http\Request;
use App\Services\GitHubService;
use App\Jobs\SystemUpdateJob;
use App\Models\CentralSetting;
use App\Models\UpdateLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class SystemUpdateController extends Controller
{
    public function index()
    {
        $currentVersion = CentralSetting::get('system_version', config('app.version', 'v1.0.0'));
        $release = GitHubService::getLatestRelease(true);
        $latestVersion = $release['tag_name'] ?? $currentVersion;

        // Fetch all available releases for the dropdown
        $allReleases = GitHubService::getAllReleases(true);

        if ($release && !empty($release['tag_name'])) {
            $releaseList = CentralSetting::getJson('github_releases', []);
            $releaseCollection = collect($releaseList)->keyBy('tag_name');
            
            if (!$releaseCollection->has($release['tag_name'])) {
                $releaseCollection->put($release['tag_name'], $release);
                CentralSetting::setJson('github_releases', $releaseCollection->values()->all());
            }
        }

        // Normalize versions by removing 'v' prefix for comparison
        $v1 = ltrim($latestVersion, 'vV');
        $v2 = ltrim($currentVersion, 'vV');

        $hasUpdate = version_compare($v1, $v2, '>');
        $autoUpdate = CentralSetting::get('github_auto_update', config('services.github.auto_update', false));
        
        $rollbackAvailable = false;
        $rollbackVersion = CentralSetting::get('latest_stable_version');
        $backupPath = CentralSetting::get('latest_stable_backup');
        
        if ($backupPath && File::exists($backupPath)) {
            $rollbackAvailable = true;
        }

        return view('central.admin.system-updater', compact(
            'currentVersion', 
            'latestVersion', 
            'hasUpdate', 
            'release', 
            'allReleases',
            'autoUpdate', 
            'rollbackAvailable', 
            'rollbackVersion'
        ));
    }

    public function rollback(Request $request)
    {
        $backupPath = CentralSetting::get('latest_stable_backup');
        if (!$backupPath || !File::exists($backupPath)) {
            return response()->json(['success' => false, 'message' => 'No rollback point available.']);
        }

        $updateId = Str::uuid()->toString();
        ManualRollbackJob::dispatch($updateId, $backupPath);

        return response()->json([
            'success' => true,
            'update_id' => $updateId,
            'message' => 'Rollback job initiated. Please wait...'
        ]);
    }

    public function toggleAutoUpdate(Request $request)
    {
        $enabled = $request->boolean('enabled');
        CentralSetting::set('github_auto_update', $enabled);

        return response()->json(['success' => true, 'enabled' => $enabled]);
    }

    public function trigger(Request $request)
    {
        $version = $request->input('version');
        
        if ($version) {
            $release = GitHubService::getReleaseByTag($version);
        } else {
            $release = GitHubService::getLatestRelease(true);
        }

        if (!$release || empty($release['zipball_url'])) {
            return response()->json(['success' => false, 'message' => 'Unable to fetch the selected release zipball URL.']);
        }

        // Check for lock
        if (CentralSetting::get('is_system_updating')) {
            return response()->json(['success' => false, 'message' => 'A system update is already in progress.']);
        }

        $updateId = Str::uuid()->toString();

        // Dispatch the job
        SystemUpdateJob::dispatch($updateId, $release['zipball_url'], $release['tag_name']);

        return response()->json([
            'success' => true,
            'update_id' => $updateId,
            'message' => "Update to {$release['tag_name']} dispatched. Please wait while the system updates."
        ]);
    }

    public function logs(Request $request, $updateId)
    {
        $logs = UpdateLog::where('update_id', $updateId)->orderBy('created_at', 'asc')->orderBy('id', 'asc')->get();
        // Check if there's a finalized message indicating completion or error
        $isFinished = $logs->contains(function ($log) {
            $message = strtolower($log->message);

            return str_contains($message, 'has been completed successfully')
                || str_contains($message, 'rollback completed')
                || str_contains($message, 'rollback has been completed successfully')
                || str_contains($message, 'fatal update error')
                || str_contains($message, 'critical: rollback failed')
                || str_contains($message, 'rollback error');
        });

        return response()->json([
            'logs' => $logs->pluck('message'),
            'finished' => $isFinished
        ]);
    }
}
