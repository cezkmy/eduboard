<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GitHubService
{
    /**
     * Fetch the latest release tag from the GitHub repository.
     * Caches the result for 24 hours to avoid rate limiting.
     *
     * @return array|null
     */
    public static function getLatestRelease()
    {
        $fetch = function () {
            return Cache::remember('github_latest_release', now()->addHours(24), function () {
                try {
                    $owner = 'cezkmy';
                    $repo = 'eduboard';
                    $url = "https://api.github.com/repos/{$owner}/{$repo}/releases/latest";

                    $response = Http::withHeaders([
                        'User-Agent' => 'EduBoard-Version-Tracker'
                    ])->get($url);

                    if ($response->successful()) {
                        $data = $response->json();
                        return [
                            'tag_name' => $data['tag_name'] ?? 'v1.0.0',
                            'name' => $data['name'] ?? 'Initial Release',
                            'body' => $data['body'] ?? 'No release notes available.',
                            'published_at' => $data['published_at'] ?? now()->toDateTimeString(),
                            'html_url' => $data['html_url'] ?? "https://github.com/{$owner}/{$repo}",
                        ];
                    }

                    Log::warning('GitHub API call failed: ' . $response->status());
                    return null;
                } catch (\Exception $e) {
                    Log::error('GitHubService Error: ' . $e->getMessage());
                    return null;
                }
            });
        };

        // If we are in a tenant context, we run this in central to avoid cache tagging issues
        if (function_exists('tenancy') && tenancy()->initialized) {
            return tenancy()->central($fetch);
        }

        return $fetch();
    }

    /**
     * Get only the version string (tag_name).
     *
     * @return string
     */
    public static function getLatestVersion()
    {
        $release = self::getLatestRelease();
        return $release['tag_name'] ?? 'v2.0.0-stable';
    }
}
