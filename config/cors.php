<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | The homepage rails are fetched from the same site, so they do not need
    | permissive cross-origin access. Keep API CORS restricted to the public
    | We Offer Wellness domains.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'OPTIONS'],

    'allowed_origins' => [
        'https://weofferwellness.co.uk',
        'https://www.weofferwellness.co.uk',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
