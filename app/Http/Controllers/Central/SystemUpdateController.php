<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GitHubService;
use App\Jobs\SystemUpdateJob;
use App\Models\UpdateLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class SystemUpdateController extends Controller
{
    public function index()
    {
        $currentVersion = config('app.version', 'v1.0.0');
        $release = GitHubService::getLatestRelease(true);
        $latestVersion = $release['tag_name'] ?? $currentVersion;

        $hasUpdate = version_compare($latestVersion, $currentVersion, '>');

        return view('central.admin.system-updater', compact('currentVersion', 'latestVersion', 'hasUpdate', 'release'));
    }

    public function trigger(Request $request)
    {
        $release = GitHubService::getLatestRelease();
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
            return str_contains(strtolower($log->message), 'has been completed successfully') || str_contains(strtolower($log->message), 'rollback completed');
        });

        return response()->json([
            'logs' => $logs->pluck('message'),
            'finished' => $isFinished
        ]);
    }
}
