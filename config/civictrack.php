<?php

return [
    'privacy_notice_version' => env('CIVICTRACK_PRIVACY_NOTICE_VERSION', '2026-11'),
    'map' => [
        'latitude' => (float) env('CIVICTRACK_MAP_LATITUDE', 0),
        'longitude' => (float) env('CIVICTRACK_MAP_LONGITUDE', 0),
        'tile_url' => env('CIVICTRACK_MAP_TILE_URL', 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'),
        'attribution' => env('CIVICTRACK_MAP_ATTRIBUTION', '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'),
    ],
    'workforce' => [
        'active_task_limit' => (int) env('CIVICTRACK_WORKER_ACTIVE_TASK_LIMIT', 8),
    ],
    'feedback' => [
        'reopen_window_days' => (int) env('CIVICTRACK_REOPEN_WINDOW_DAYS', 30),
    ],
    'bootstrap_admin' => [
        'name' => env('CIVICTRACK_ADMIN_NAME'),
        'email' => env('CIVICTRACK_ADMIN_EMAIL'),
        'password' => env('CIVICTRACK_ADMIN_PASSWORD'),
    ],
];
