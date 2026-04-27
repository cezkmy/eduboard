<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use BadMethodCallException;

class GitHubService
{
    protected static function initCurl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Laravel-App");
        
        $token = config('services.github.token');
        if ($token) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: token {$token}",
                "Accept: application/vnd.github.v3+json"
            ]);
        }
        
        // Disable SSL verification only in local environment
        $isLocal = app()->environment('local') || in_array(request()->getHost(), ['localhost', '127.0.0.1']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$isLocal);
        
        return $ch;
    }

    protected static function fetchLatestReleaseFromApi(): ?array
    {
        try {
            $repo = config('services.github.repo', 'cezkmy/eduboard');
            $url = "https://api.github.com/repos/{$repo}/releases/latest";

            $ch = self::initCurl($url);
            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                Log::error("Curl Error: " . curl_error($ch));
                curl_close($ch);
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

            $ch = self::initCurl($url);
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

    protected static function fetchAllReleasesFromApi(): array
    {
        try {
            $repo = config('services.github.repo', 'cezkmy/eduboard');
            
            // 1. Fetch formal Releases
            $releasesUrl = "https://api.github.com/repos/{$repo}/releases";
            $ch = self::initCurl($releasesUrl);
            $releasesResponse = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 401) {
                Log::warning("GitHub API returned 401 (Unauthorized). Retrying without token...");
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $releasesUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, "Laravel-App");
                $isLocal = app()->environment('local') || in_array(request()->getHost(), ['localhost', '127.0.0.1']);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$isLocal);
                $releasesResponse = curl_exec($ch);
                curl_close($ch);
            }

            $releases = json_decode($releasesResponse, true);
            $formattedReleases = collect(is_array($releases) ? $releases : [])->map(function ($release) use ($repo) {
                return [
                    'tag_name' => $release['tag_name'] ?? null,
                    'name' => $release['name'] ?? 'Release',
                    'body' => $release['body'] ?? 'No release notes available.',
                    'published_at' => $release['published_at'] ?? now()->toDateTimeString(),
                    'html_url' => $release['html_url'] ?? "https://github.com/{$repo}",
                    'zipball_url' => $release['zipball_url'] ?? null,
                    'is_prerelease' => $release['prerelease'] ?? false,
                ];
            })->filter(fn($r) => !empty($r['tag_name']));

            // 2. Fetch Tags (as backup for missing releases)
            $tagsUrl = "https://api.github.com/repos/{$repo}/tags";
            $ch = self::initCurl($tagsUrl);
            $tagsResponse = curl_exec($ch);
            curl_close($ch);
            $tags = json_decode($tagsResponse, true);

            $existingTags = $formattedReleases->pluck('tag_name')->toArray();
            
            if (is_array($tags)) {
                foreach ($tags as $tag) {
                    if (!in_array($tag['name'], $existingTags)) {
                        $formattedReleases->push([
                            'tag_name' => $tag['name'],
                            'name' => $tag['name'],
                            'body' => 'Tag available (no release notes yet).',
                            'published_at' => now()->toDateTimeString(),
                            'html_url' => "https://github.com/{$repo}/releases/tag/{$tag['name']}",
                            'zipball_url' => $tag['zipball_url'] ?? null,
                            'is_prerelease' => false,
                        ]);
                    }
                }
            }

            return $formattedReleases->filter(fn($r) => !empty($r['tag_name']))->values()->all();
        } catch (\Exception $e) {
            Log::error('GitHubService Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a list of all releases from the GitHub repository.
     *
     * @param bool $force
     * @return array
     */
    public static function getAllReleases($force = false)
    {
        $cacheKey = 'github_all_releases';

        // Bypass cache in tenant context to avoid tagging issues
        if (function_exists('tenancy') && tenancy()->initialized) {
            return self::fetchAllReleasesFromApi();
        }

        if ($force) {
            Cache::forget($cacheKey);
        }

        try {
            return Cache::remember($cacheKey, now()->addMinutes(60), function () {
                return self::fetchAllReleasesFromApi();
            });
        } catch (BadMethodCallException $e) {
            if (str_contains($e->getMessage(), 'does not support tagging')) {
                return self::fetchAllReleasesFromApi();
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('GitHubService Error: ' . $e->getMessage());
            return self::fetchAllReleasesFromApi();
        }
    }
}
