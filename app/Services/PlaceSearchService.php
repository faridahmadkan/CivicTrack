<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class PlaceSearchService
{
    private const string RATE_LIMIT_KEY = 'civictrack:place-search:nominatim';

    private const string RATE_LIMIT_LOCK_KEY = 'civictrack:place-search:nominatim-lock';

    /**
     * @return array<int, array{label: string, latitude: float, longitude: float}>|null
     */
    public function search(string $query): ?array
    {
        $searchUrl = (string) config('civictrack.map.place_search.url');

        if (! config('civictrack.map.place_search.enabled')
            || ! config('civictrack.map.place_search.configured')
            || blank($searchUrl)) {
            return null;
        }

        $normalizedQuery = Str::lower(trim($query));
        $cacheKey = 'civictrack:place-search:'.hash('sha256', $normalizedQuery);
        $cachedResults = Cache::get($cacheKey);

        if (is_array($cachedResults)) {
            return $cachedResults;
        }

        $rateLimitLock = Cache::lock(self::RATE_LIMIT_LOCK_KEY, 2);

        if (! $rateLimitLock->get()) {
            throw new TooManyRequestsHttpException(1, 'Place search is rate limited. Wait a moment and try again.');
        }

        try {
            if (RateLimiter::tooManyAttempts(self::RATE_LIMIT_KEY, 1)) {
                throw new TooManyRequestsHttpException(1, 'Place search is rate limited. Wait a moment and try again.');
            }

            RateLimiter::hit(self::RATE_LIMIT_KEY, 1);
        } finally {
            $rateLimitLock->release();
        }

        try {
            $response = Http::acceptJson()
                ->withUserAgent('CivicTrack/3.0 ('.rtrim((string) config('app.url'), '/').')')
                ->withHeaders(['Referer' => rtrim((string) config('app.url'), '/').'/'])
                ->connectTimeout(3)
                ->timeout(6)
                ->get($searchUrl, [
                    'format' => 'jsonv2',
                    'q' => trim($query),
                    'limit' => 5,
                ]);
        } catch (ConnectionException|InvalidArgumentException) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $results = collect($response->json())
            ->filter(static fn (mixed $place): bool => is_array($place)
                && is_string($place['display_name'] ?? null)
                && is_numeric($place['lat'] ?? null)
                && is_numeric($place['lon'] ?? null))
            ->map(static function (array $place): ?array {
                $latitude = (float) $place['lat'];
                $longitude = (float) $place['lon'];

                if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
                    return null;
                }

                return [
                    'label' => Str::limit($place['display_name'], 255, ''),
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ];
            })
            ->filter()
            ->values()
            ->all();

        Cache::put(
            $cacheKey,
            $results,
            now()->addMinutes(max(1, (int) config('civictrack.map.place_search.cache_minutes', 720))),
        );

        return $results;
    }
}
