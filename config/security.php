<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Authentication
    |--------------------------------------------------------------------------
    |
    | When enabled, all /api/v1 routes require either a valid Sanctum bearer
    | token or a matching X-Api-Key header. Disabled by default so local
    | development and existing integrations keep working unchanged.
    |
    */

    'api' => [
        'auth_required' => env('SECURITY_API_AUTH_REQUIRED', false),
        'key' => env('SECURITY_API_KEY'),
        'allow_sanctum_tokens' => env('SECURITY_ALLOW_SANCTUM_TOKENS', true),
        'log_failed_attempts' => env('SECURITY_LOG_FAILED_AUTH', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting (requests per minute)
    |--------------------------------------------------------------------------
    */

    'rate_limits' => [
        'read' => (int) env('SECURITY_RATE_LIMIT_READ', 120),
        'import' => (int) env('SECURITY_RATE_LIMIT_IMPORT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Constraints
    |--------------------------------------------------------------------------
    */

    'upload' => [
        'max_size_kb' => (int) env('SECURITY_UPLOAD_MAX_KB', 10240),
        'max_rows' => (int) env('SECURITY_UPLOAD_MAX_ROWS', 50000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Response Headers
    |--------------------------------------------------------------------------
    */

    'headers' => [
        'enabled' => env('SECURITY_HEADERS_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Credentials
    |--------------------------------------------------------------------------
    |
    | When API auth is required, the dashboard injects these credentials into
    | page metadata so same-origin browser requests continue to work.
    |
    */

    'dashboard' => [
        'bearer_token' => env('SECURITY_DASHBOARD_BEARER_TOKEN'),
        'api_key' => env('SECURITY_DASHBOARD_API_KEY'),
    ],

];
