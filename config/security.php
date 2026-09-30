<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Authentication Gate
    |--------------------------------------------------------------------------
    |
    | When enabled, the dashboard requires login and API routes require a
    | session, Sanctum bearer token, or X-Api-Key. Defaults to true outside
    | local/testing so non-local environments are protected by default.
    |
    */

    'auth_required' => filter_var(
        env(
            'SECURITY_AUTH_REQUIRED',
            ! in_array(env('APP_ENV', 'production'), ['local', 'testing'], true)
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    /*
    |--------------------------------------------------------------------------
    | Allow Public Registration
    |--------------------------------------------------------------------------
    |
    | New accounts always receive the viewer role. Disable in production if
    | you only want seeded / invited users.
    |
    */

    'registration_enabled' => filter_var(
        env('SECURITY_REGISTRATION_ENABLED', true),
        FILTER_VALIDATE_BOOLEAN
    ),

    /*
    |--------------------------------------------------------------------------
    | API Authentication
    |--------------------------------------------------------------------------
    */

    'api' => [
        // Legacy alias — prefer SECURITY_AUTH_REQUIRED
        'auth_required' => filter_var(
            env(
                'SECURITY_API_AUTH_REQUIRED',
                env(
                    'SECURITY_AUTH_REQUIRED',
                    ! in_array(env('APP_ENV', 'production'), ['local', 'testing'], true)
                )
            ),
            FILTER_VALIDATE_BOOLEAN
        ),
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
    | Optional static credentials for machine/dashboard use. Prefer session
    | login for humans; use these only when needed for automation.
    |
    */

    'dashboard' => [
        'bearer_token' => env('SECURITY_DASHBOARD_BEARER_TOKEN'),
        'api_key' => env('SECURITY_DASHBOARD_API_KEY'),
    ],

];
