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

        return view('central.admin.system-updater', compact('currentVersion', 'latestVersion', 'hasUpdate', 'release', 'autoUpdate', 'rollbackAvailable', 'rollbackVersion'));
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
        $release = GitHubService::getLatestRelease(true);
        if (!$release || empty($release['zipball_url'])) {
            return response()->json(['success' => false, 'message' => 'Unable to fetch the latest release zipball URL.']);
        }

        $updateId = Str::uuid()->toString();

        // Dispatch the job
        SystemUpdateJob::dispatch($updateId, $release['zipball_url'], $release['tag_name']);

        return response()->json([
            'success' => true,
            'update_id' => $updateId,
            'message' => 'Update job dispatched. Please wait while the system updates.'
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
