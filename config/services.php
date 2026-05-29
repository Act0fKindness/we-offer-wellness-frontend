<?php

$stripeTesting = env('STRIPE_TESTING', false);
$stripeLiveKey = env('STRIPE_KEY');
$stripeLiveSecret = env('STRIPE_SECRET');
$stripeLiveWebhookSecret = env('STRIPE_WEBHOOK_SECRET');
$stripeTestPublishableKey = env('TESTING_STRIPE_PUBLISHABLE_KEY', env('TESTING_STRIPE_KEY'));
$stripeTestSecret = env('TESTING_STRIPE_SECRET', env('TESTING_STRIPE_KEY'));
$stripeTestWebhookSecret = env('TESTING_STRIPE_WEBHOOK_SECRET');
$resolvedStripeWebhookSecret = $stripeTesting
    ? ($stripeTestWebhookSecret ?: $stripeLiveWebhookSecret)
    : $stripeLiveWebhookSecret;

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'stripe' => [
        'testing' => $stripeTesting,

        // Publishable key (pk_live_... / pk_test_...)
        // Some existing environments still store the test secret in TESTING_STRIPE_KEY,
        // so keep that as a fallback while preferring TESTING_STRIPE_PUBLISHABLE_KEY.
        'key' => $stripeTesting ? $stripeTestPublishableKey : $stripeLiveKey,

        // Secret key (sk_live_... / sk_test_...)
        'secret' => $stripeTesting ? $stripeTestSecret : $stripeLiveSecret,

        // Webhook signing secret (whsec_...)
        // Keep both keys for compatibility with different codebases/packages.
        'webhook_secret' => $resolvedStripeWebhookSecret,
        'webhook' => [
            'secret' => $resolvedStripeWebhookSecret,
        ],
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'mapbox' => [
        'token' => env('MAPBOX_API_KEY'),
    ],

    'brevo' => [
        'key' => env('BREVO_API_KEY'),
    ],

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
    ],

    'google_maps' => [
        'key' => env('GOOGLE_MAPS_API_KEY'),
        'region' => env('GOOGLE_REGION', 'uk'),
    ],

    'search_console' => [
        'property_url' => env('GOOGLE_SEARCH_CONSOLE_PROPERTY_URL'),
        'sitemap_url' => env('GOOGLE_SEARCH_CONSOLE_SITEMAP_URL', rtrim(env('APP_URL', ''), '/') . '/sitemap-index.xml'),
        'sitemap_urls' => env(
            'GOOGLE_SEARCH_CONSOLE_SITEMAP_URLS',
            implode(',', array_filter([
                rtrim(env('APP_URL', ''), '/') . '/sitemap-index.xml',
                rtrim(env('APP_URL', ''), '/') . '/sitemap.xml',
                rtrim(env('APP_URL', ''), '/') . '/sitemap-pages.xml',
            ]))
        ),
        'client_id' => env('GOOGLE_SEARCH_CONSOLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_SEARCH_CONSOLE_CLIENT_SECRET'),
        'client_credentials_file' => env('GOOGLE_SEARCH_CONSOLE_CLIENT_CREDENTIALS_FILE'),
        'refresh_token' => env('GOOGLE_SEARCH_CONSOLE_REFRESH_TOKEN'),
    ],

];
