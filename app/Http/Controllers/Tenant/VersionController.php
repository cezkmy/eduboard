<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Services\GitHubService;
use Symfony\Component\Process\Process;
use ZipArchive;

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

    protected function runTenantCommand(string $command, array $parameters = []): void
    {
        $exitCode = Artisan::call($command, $parameters);
        if ($exitCode !== 0) {
            $output = trim(Artisan::output());
            throw new \RuntimeException(sprintf('Tenant command failed: %s', $output ?: 'Unknown error while running Artisan command.'));
        }
    }

    protected function runShellCommand(array $command): void
    {
        $process = new Process($command, base_path());
        $process->setTimeout(600);
        $process->run();

        if (!$process->isSuccessful()) {
            $output = trim($process->getErrorOutput() ?: $process->getOutput());
            throw new \RuntimeException(sprintf('Shell command failed (%s): %s', implode(' ', $command), $output ?: 'Unknown error.'));
        }
    }

    protected function downloadReleaseZipball(string $zipUrl, string $targetPath): void
    {
        $ch = curl_init($zipUrl);

        if (!$ch) {
            throw new \RuntimeException('Unable to initialize download request.');
        }

        $file = fopen($targetPath, 'wb');
        if (!$file) {
            throw new \RuntimeException('Unable to write update archive to disk.');
        }

        curl_setopt($ch, CURLOPT_FILE, $file);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Laravel-App');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);

        $success = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        fclose($file);
        curl_close($ch);

        if ($success === false || $status >= 400) {
            unlink($targetPath);
            throw new \RuntimeException(sprintf('Failed to download release zip (%s): %s', $zipUrl, $error ?: "HTTP {$status}"));
        }
    }

    protected function extractReleaseZipball(string $zipPath): void
    {
        if (!class_exists(ZipArchive::class)) {
            throw new \RuntimeException('PHP ZipArchive extension is required for release extraction.');
        }

        $filesystem = new Filesystem();
        $extractPath = storage_path('app/updates/' . uniqid('release_extract_', true));
        $filesystem->ensureDirectoryExists($extractPath);

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Unable to open release zip archive.');
        }

        if (!$zip->extractTo($extractPath)) {
            $zip->close();
            throw new \RuntimeException('Unable to extract release zip archive.');
        }

        $zip->close();

        $contents = array_values(array_diff(scandir($extractPath), ['.', '..']));
        $sourcePath = $extractPath;

        if (count($contents) === 1 && is_dir($extractPath . DIRECTORY_SEPARATOR . $contents[0])) {
            $sourcePath = $extractPath . DIRECTORY_SEPARATOR . $contents[0];
        }

        if (!$filesystem->copyDirectory($sourcePath, base_path())) {
            throw new \RuntimeException('Unable to copy release files into application path.');
        }

        $filesystem->deleteDirectory($extractPath);
    }

    protected function applyReleaseZip(string $version, string $zipUrl): void
    {
        $filesystem = new Filesystem();
        $downloadDir = storage_path('app/updates');
        $filesystem->ensureDirectoryExists($downloadDir);

        $zipPath = $downloadDir . DIRECTORY_SEPARATOR . 'release-' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $version) . '.zip';

        $this->downloadReleaseZipball($zipUrl, $zipPath);

        try {
            $this->extractReleaseZipball($zipPath);
        } finally {
            if ($filesystem->exists($zipPath)) {
                $filesystem->delete($zipPath);
            }
        }
    }

    protected function npmCommand(array $arguments): array
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return array_merge(['cmd', '/c', 'npm'], $arguments);
        }

        return array_merge(['npm'], $arguments);
    }

    /**
     * Apply the latest GitHub release version to the current tenant.
     */
    public function applyUpdate(Request $request)
    {
        $this->ensurePermission();

        $tenant = tenant();
        $requested = $request->input('version');
        $release = null;

        if ($requested) {
            $release = GitHubService::getReleaseByTag($requested);
        }

        if (!$release) {
            $release = GitHubService::getLatestRelease(true);
        }

        $latestVersion = $requested ?: ($release['tag_name'] ?? null);

        if (!$latestVersion) {
            return response()->json(['success' => false, 'message' => 'Unable to fetch release details.']);
        }

        if ($tenant->system_version === $latestVersion) {
            return response()->json(['success' => false, 'message' => 'System is already on this version.']);
        }

        if (empty($release['zipball_url'])) {
            return response()->json(['success' => false, 'message' => 'Release archive is unavailable.']);
        }

        $updateId = \Illuminate\Support\Str::uuid()->toString();

        \App\Jobs\TenantUpdateJob::dispatch(
            $updateId,
            $tenant->id,
            $latestVersion,
            $release['zipball_url']
        );

        return response()->json([
            'success' => true,
            'update_id' => $updateId,
            'message' => "Update to {$latestVersion} started in background."
        ]);
    }

    /**
     * Rollback the current tenant to the previous version.
     */
    public function rollback(Request $request)
    {
        $this->ensurePermission();
        $tenant = tenant();

        if (empty($tenant->previous_version) || empty($tenant->latest_backup_path)) {
            return response()->json(['success' => false, 'message' => 'No rollback point available.']);
        }

        $updateId = \Illuminate\Support\Str::uuid()->toString();

        \App\Jobs\TenantRollbackJob::dispatch(
            $updateId,
            $tenant->id
        );

        return response()->json([
            'success' => true,
            'update_id' => $updateId,
            'message' => "Rollback to {$tenant->previous_version} started in background."
        ]);
    }

    /**
     * Get logs for a specific update session.
     */
    public function logs($updateId)
    {
        $this->ensurePermission();
        
        $logs = \App\Models\UpdateLog::where('update_id', $updateId)
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $isFinished = $logs->contains(function ($log) {
            $msg = strtolower($log->message);
            return str_contains($msg, 'successfully') || 
                   str_contains($msg, 'fatal update error') ||
                   str_contains($msg, 'rollback error');
        });

        return response()->json([
            'logs' => $logs->pluck('message'),
            'finished' => $isFinished
        ]);
    }
}
