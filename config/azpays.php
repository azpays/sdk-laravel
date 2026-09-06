<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | AzPays API Key
    |--------------------------------------------------------------------------
    |
    | Your AzPays Merchant API Key. All API calls are authenticated with this
    | key. You can find your API key in the AzPays Merchant Dashboard.
    |
    */
    'api_key' => env('AZPAYS_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the AzPays API. Defaults to the production API.
    | For local testing or sandbox, point this to your mock/test server.
    |
    */
    'base_url' => env('AZPAYS_BASE_URL', 'https://api.azpays.net'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Timeout
    |--------------------------------------------------------------------------
    |
    | The maximum number of seconds to wait for a response from the AzPays API.
    |
    */
    'timeout' => (int) env('AZPAYS_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Max Retries
    |--------------------------------------------------------------------------
    |
    | Number of automatic retries using exponential backoff on 429 rate limit
    | responses or 5xx server errors.
    |
    */
    'max_retries' => (int) env('AZPAYS_MAX_RETRIES', 3),

    /*
    |--------------------------------------------------------------------------
    | Debug Logging
    |--------------------------------------------------------------------------
    |
    | When enabled, logs all outgoing requests and responses using error_log.
    |
    */
    'debug' => (bool) env('AZPAYS_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Webhooks Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for handling incoming AzPays webhooks.
    |
    */
    'webhook' => [
        // Webhook signing secret used to verify HMAC-SHA256 signatures
        'secret' => env('AZPAYS_WEBHOOK_SECRET', ''),

        // The URL path where webhooks should be received
        'path' => env('AZPAYS_WEBHOOK_PATH', 'azpays/webhook'),

        // Maximum allowed timestamp drift in seconds to prevent replay attacks (default 5 minutes)
        'tolerance' => (int) env('AZPAYS_WEBHOOK_TOLERANCE', 300),

        // Optional middleware to apply to the webhook route
        'middleware' => [],
    ],
];
