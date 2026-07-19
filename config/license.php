<?php

return [
    // ───── HotelHub vendor licensing (existing — for tenants of this HMS) ─────
    'vendor_base_url'    => env('LICENSE_VENDOR_BASE_URL', 'https://vendor.hotelhub.id'),
    'public_key_path'    => env('LICENSE_PUBLIC_KEY_PATH', 'config/license/vendor-public.pem'),
    'public_key_sha256'  => env('LICENSE_PUBLIC_KEY_HASH'),
    'grace_days'         => (int) env('LICENSE_GRACE_DAYS', 30),
    'cache_ttl_seconds'  => 300,

    'pairing_endpoint'   => '/api/license/pair',
    'heartbeat_endpoint' => '/api/license/heartbeat',
    'migrate_endpoint'   => '/api/license/migrate',
    'unpair_endpoint'    => '/api/license/unpair',

    // ───── whitelabel.co.id marketplace (kit v3 — buyer activation of this app) ─────
    'marketplace' => [
        'server_url'         => env('LICENSE_SERVER_URL', 'https://whitelabel.co.id'),
        'public_key_path'    => public_path('marketplace.public.pem'),
        'lock_file'          => storage_path('app/.license.lock'),
        'heartbeat_interval' => (int) env('LICENSE_HEARTBEAT_INTERVAL', 86400),
        'heartbeat_grace'    => (int) env('LICENSE_HEARTBEAT_GRACE', 604800),
        'http_timeout'       => 10,
    ],
];
