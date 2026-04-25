<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use BadMethodCallException;

class GitHubService
{
    protected static function fetchLatestReleaseFromApi(): ?array
    {
        try {
            $repo = config('services.github.repo', 'cezkmy/eduboard');
            $url = "https://api.github.com/repos/{$repo}/releases/latest";

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, "Laravel-App"); // REQUIRED
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // for localhost only

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                Log::error("Curl Error: " . curl_error($ch));
                return null;
            }

            curl_close($ch);

            $latest = json_decode($response, true);

            if (!empty($latest['tag_name'])) {
                return [
                    'tag_name' => $latest['tag_name'] ?? null,
                    'name' => $latest['name'] ?? 'Release',
                    'body' => $latest['body'] ?? 'No release notes available.',
                    'published_at' => $latest['published_at'] ?? now()->toDateTimeString(),
                    'html_url' => $latest['html_url'] ?? "https://github.com/" . $repo,
                    'zipball_url' => $latest['zipball_url'] ?? null,
                    'is_prerelease' => $latest['prerelease'] ?? false,
                ];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('GitHubService Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetch the latest release tag from the GitHub repository.
     * Caches the result for 30 minutes to avoid rate limiting.
     *
     * @return array|null
     */
    public static function getLatestRelease($force = false)
    {
        $cacheKey = 'github_latest_release';
        
        // In tenant context, Stancl Tenancy may enforce cache tags even when the
        // underlying store doesn't support them. To avoid hard failures, we
        // bypass Cache entirely and fetch directly.
        if (function_exists('tenancy') && tenancy()->initialized) {
            return self::fetchLatestReleaseFromApi();
        }

        if ($force) {
            Cache::forget($cacheKey);
        }

        $fetch = function () use ($cacheKey) {
            try {
                return Cache::remember($cacheKey, now()->addMinutes(30), function () {
                    return self::fetchLatestReleaseFromApi();
                });
            } catch (BadMethodCallException $e) {
                if (str_contains($e->getMessage(), 'does not support tagging')) {
                    // Tenancy cache wrapper may require tags on stores that don't support them.
                    return self::fetchLatestReleaseFromApi();
                }
                throw $e;
            }
        };

        return $fetch();
    }

    /**
     * Fetch a specific release by tag name.
     *
     * @param string $tag
     * @return array|null
     */
    public static function getReleaseByTag(string $tag)
    {
        if (empty($tag)) {
            return null;
        }

        try {
            $repo = config('services.github.repo', 'cezkmy/eduboard');
            $url = "https://api.github.com/repos/{$repo}/releases/tags/{$tag}";

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, "Laravel-App");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                Log::error("Curl Error: " . curl_error($ch));
                curl_close($ch);
                return null;
            }

            curl_close($ch);

            $release = json_decode($response, true);

            if (!empty($release['tag_name'])) {
                return [
                    'tag_name' => $release['tag_name'] ?? null,
                    'name' => $release['name'] ?? 'Release',
                    'body' => $release['body'] ?? 'No release notes available.',
                    'published_at' => $release['published_at'] ?? now()->toDateTimeString(),
                    'html_url' => $release['html_url'] ?? "https://github.com/{$repo}",
                    'zipball_url' => $release['zipball_url'] ?? null,
                    'is_prerelease' => $release['prerelease'] ?? false,
                ];
            }
        } catch (\Exception $e) {
            Log::error('GitHubService Error: ' . $e->getMessage());
        }

        return null;
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
