<?php

$streetTileUrl = str_replace(
    'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
    (string) env('CIVICTRACK_MAP_TILE_URL', 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'),
);
$streetAttribution = str_replace(
    '&copy; OpenStreetMap contributors',
    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    (string) env('CIVICTRACK_MAP_ATTRIBUTION', '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'),
);
$streetTileParts = parse_url($streetTileUrl);

if (! is_array($streetTileParts)
    || ! in_array($streetTileParts['scheme'] ?? null, ['http', 'https'], true)
    || blank($streetTileParts['host'] ?? null)
    || isset($streetTileParts['user'])
    || isset($streetTileParts['pass'])
    || isset($streetTileParts['query'])
    || isset($streetTileParts['fragment'])) {
    $streetTileUrl = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
    $streetAttribution = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
}

$satelliteTileUrl = (string) env('CIVICTRACK_MAP_SATELLITE_TILE_URL', 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}');
$satelliteTileParts = parse_url($satelliteTileUrl);

if (! is_array($satelliteTileParts)
    || ! in_array($satelliteTileParts['scheme'] ?? null, ['http', 'https'], true)
    || blank($satelliteTileParts['host'] ?? null)
    || isset($satelliteTileParts['user'])
    || isset($satelliteTileParts['pass'])
    || isset($satelliteTileParts['query'])
    || isset($satelliteTileParts['fragment'])) {
    $satelliteTileUrl = '';
}

$placeSearchUrl = (string) env('CIVICTRACK_PLACE_SEARCH_URL', 'https://nominatim.openstreetmap.org/search');
$placeSearchUrlParts = parse_url($placeSearchUrl);
$placeSearchEnabled = (bool) env('CIVICTRACK_PLACE_SEARCH_ENABLED', true);
$placeSearchConfigured = is_array($placeSearchUrlParts)
    && in_array($placeSearchUrlParts['scheme'] ?? null, ['http', 'https'], true)
    && filled($placeSearchUrlParts['host'] ?? null)
    && ! isset($placeSearchUrlParts['user'])
    && ! isset($placeSearchUrlParts['pass'])
    && ! isset($placeSearchUrlParts['query'])
    && ! isset($placeSearchUrlParts['fragment']);

return [
    'privacy_notice_version' => env('CIVICTRACK_PRIVACY_NOTICE_VERSION', '2026-11-23'),
    'map' => [
        'latitude' => (float) env('CIVICTRACK_MAP_LATITUDE', 0),
        'longitude' => (float) env('CIVICTRACK_MAP_LONGITUDE', 0),
        'street_tile_url' => $streetTileUrl,
        'street_attribution' => $streetAttribution,
        'satellite_tile_url' => $satelliteTileUrl,
        'satellite_attribution' => env('CIVICTRACK_MAP_SATELLITE_ATTRIBUTION', 'Sources: Esri, DigitalGlobe, GeoEye, i-cubed, USDA FSA, USGS, AEX, Getmapping, Aerogrid, IGN, IGP, swisstopo, and the GIS User Community'),
        'place_search' => [
            'enabled' => $placeSearchEnabled,
            'configured' => $placeSearchConfigured,
            'url' => $placeSearchUrl,
            'name' => env('CIVICTRACK_PLACE_SEARCH_NAME', 'OpenStreetMap Nominatim'),
            'attribution' => env('CIVICTRACK_PLACE_SEARCH_ATTRIBUTION', '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap contributors</a>, via <a href="https://nominatim.openstreetmap.org/" target="_blank" rel="noopener">Nominatim</a>'),
            'cache_minutes' => (int) env('CIVICTRACK_PLACE_SEARCH_CACHE_MINUTES', 720),
        ],
    ],
    'workforce' => [
        'active_task_limit' => (int) env('CIVICTRACK_WORKER_ACTIVE_TASK_LIMIT', 8),
    ],
    'feedback' => [
        'reopen_window_days' => (int) env('CIVICTRACK_REOPEN_WINDOW_DAYS', 30),
    ],
    'bootstrap_admin' => [
        'name' => 'Farid Ahmad Khan',
        'email' => 'farid@civictrack.com',
        'password' => env('CIVICTRACK_ADMIN_PASSWORD'),
    ],
    'demo' => [
        'enabled' => (bool) env('CIVICTRACK_SEED_DEMO', false),
        'password' => env('CIVICTRACK_DEMO_PASSWORD'),
    ],
];
