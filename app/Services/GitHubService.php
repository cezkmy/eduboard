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
    public static function getLatestRelease($force = false)
    {
        $cacheKey = 'github_latest_release';
        
        if ($force) {
            Cache::forget($cacheKey);
        }

        $fetch = function () use ($cacheKey) {
            return Cache::remember($cacheKey, now()->addMinutes(30), function () {
                try {
                    $repo = config('services.github.repo', 'cezkmy/eduboard');
                    // We use /releases instead of /releases/latest to include all releases 
                    // and manually pick the one with the highest version number
                    $url = "https://api.github.com/repos/{$repo}/releases";

                    $headers = [
                        'User-Agent' => 'EduBoard-Version-Tracker',
                        'Accept' => 'application/vnd.github.v3+json'
                    ];

                    if ($token = config('services.github.token')) {
                        $headers['Authorization'] = "token {$token}";
                    }

                    $response = Http::withHeaders($headers)->get($url);

                    if ($response->successful()) {
                        $releases = $response->json();
                        
                        if (empty($releases)) {
                            return null;
                        }

                        // Filter out drafts and sort by version number
                        $latest = collect($releases)
                            ->filter(fn($r) => !($r['draft'] ?? false))
                            ->sort(function($a, $b) {
                                $v1 = ltrim($a['tag_name'] ?? '0.0.0', 'vV');
                                $v2 = ltrim($b['tag_name'] ?? '0.0.0', 'vV');
                                return version_compare($v2, $v1); // Descending (highest version first)
                            })
                            ->first();

                        if (!$latest) return null;

                        return [
                            'tag_name' => $latest['tag_name'] ?? null,
                            'name' => $latest['name'] ?? 'Release',
                            'body' => $latest['body'] ?? 'No release notes available.',
                            'published_at' => $latest['published_at'] ?? now()->toDateTimeString(),
                            'html_url' => $latest['html_url'] ?? "https://github.com/" . config('services.github.repo', 'cezkmy/eduboard'),
                            'zipball_url' => $latest['zipball_url'] ?? null,
                            'is_prerelease' => $latest['prerelease'] ?? false
                        ];
                    }

                    Log::warning('GitHub API call failed: ' . $response->status() . ' - URL: ' . $url);
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
        return $release['tag_name'] ?? config('app.version', 'v1.0.0');
    }
}
