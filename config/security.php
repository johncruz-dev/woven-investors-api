<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Authentication Gate
    |--------------------------------------------------------------------------
    |
    | When enabled, education routes require login. Defaults to true outside
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
    | New accounts always receive the student role. Disable in production if
    | you only want seeded / invited users.
    |
    */

    'registration_enabled' => filter_var(
        env('SECURITY_REGISTRATION_ENABLED', true),
        FILTER_VALIDATE_BOOLEAN
    ),

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

];
