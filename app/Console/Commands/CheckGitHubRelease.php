<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GitHubService;
use App\Models\CentralSetting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class CheckGitHubRelease extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eduboard:check-github';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically check for new GitHub releases and notify tenants.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Checking for new GitHub releases...");

        // Force fetch the latest release from GitHub
        $latestRelease = GitHubService::getLatestRelease(true);

        if (!$latestRelease) {
            $this->error("Failed to fetch release from GitHub.");
            return 1;
        }

        $latestTagName = $latestRelease['tag_name'];
        $lastNotifiedVersion = CentralSetting::get('last_notified_version');

        $this->line("Latest version on GitHub: " . $latestTagName);
        $this->line("Last notified version: " . ($lastNotifiedVersion ?? 'None'));

        if ($latestTagName !== $lastNotifiedVersion) {
            $this->info("New version detected! Preparing to broadcast {$latestTagName}...");

            // Trigger the actual broadcast to all active tenants
            try {
                Artisan::call('eduboard:release', [
                    'version' => $latestTagName
                ]);

                // Update the last notified version so we don't spam
                CentralSetting::set('last_notified_version', $latestTagName);
                // Keep the central system version in sync for the dashboard
                CentralSetting::set('system_version', $latestTagName);

                // Notify Central Admins
                $centralAdmins = \App\Models\User::where('is_admin', true)->get();
                foreach ($centralAdmins as $admin) {
                    $admin->notify(new \App\Notifications\CentralSystemUpdateNotification($latestRelease));
                }

                $this->info("Successfully broadcasted {$latestTagName} automatically.");
            } catch (\Exception $e) {
                Log::error("Auto-broadcast failed for {$latestTagName}: " . $e->getMessage());
                $this->error("Auto-broadcast failed. Check logs.");
                return 1;
            }
        } else {
            $this->info("No new version detected. Everything is up to date.");
        }

        // Always record the last check time
        CentralSetting::set('last_github_check_at', now()->toDateTimeString());

        return 0;
    }
}
