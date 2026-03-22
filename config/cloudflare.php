<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cloudflare Stream — Direct Creator Uploads
    |--------------------------------------------------------------------------
    */

    'stream' => [
        'account_id'          => env('CF_STREAM_ACCOUNT_ID', ''),
        'api_token'           => env('CF_STREAM_API_TOKEN', ''),
        'customer_subdomain'  => env('CF_STREAM_CUSTOMER_SUBDOMAIN', ''),
        'max_duration_seconds'=> (int) env('CF_STREAM_MAX_DURATION', 3600),
        'allowed_origins'     => array_filter(explode(',', env('CF_STREAM_ALLOWED_ORIGINS', env('APP_URL', '')))),
        'enabled'             => env('CF_STREAM_ENABLED', false),
        'webhook_secret'      => env('CF_STREAM_WEBHOOK_SECRET', ''),
    ],
];
