<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Logbook Enabled
    |--------------------------------------------------------------------------
    |
    | This option controls whether the logbook middleware is enabled and 
    | actively logging requests.
    |
    */
    'enabled' => env('LOGBOOK_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | UI Panel Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the admin UI panel
    |
    */
    'ui_enabled' => env('LOGBOOK_UI_ENABLED', true),
    'ui_route_prefix' => env('LOGBOOK_UI_PREFIX', 'logbook'),

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    |
    | Static credentials for accessing the logbook panel
    |
    */
    'auth_user' => env('LOGBOOK_USER', 'admin'),
    'auth_pass' => env('LOGBOOK_PASS', 'password'),

    /*
    |--------------------------------------------------------------------------
    | Data Masking
    |--------------------------------------------------------------------------
    |
    | Fields to mask in request/response bodies for security
    |
    */
    'mask_fields' => [
        'password',
        'password_confirmation',
        'token',
        'api_key',
        'secret',
        'access_token',
        'refresh_token',
        'credit_card',
        'ssn',
        'cvv',
    ],

    /*
    |--------------------------------------------------------------------------
    | Body Truncation
    |--------------------------------------------------------------------------
    |
    | Maximum size for request/response body storage (in bytes)
    |
    */
    'truncate_body_at' => env('LOGBOOK_TRUNCATE_AT', 10240), // 10KB

    /*
    |--------------------------------------------------------------------------
    | Data Retention
    |--------------------------------------------------------------------------
    |
    | Automatically delete logs older than this many days
    |
    */
    'retention_days' => env('LOGBOOK_RETENTION_DAYS', 90),

    /*
    |--------------------------------------------------------------------------
    | Excluded Routes
    |--------------------------------------------------------------------------
    |
    | Routes that should not be logged
    |
    */
    'excluded_routes' => [
        'logbook/*',
        'telescope/*',
        'horizon/*',
        '_debugbar/*',
        'health-check',
        'up',
    ],

    /*
    |--------------------------------------------------------------------------
    | Included HTTP Methods
    |--------------------------------------------------------------------------
    |
    | HTTP methods to log
    |
    */
    'included_methods' => [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | Database connection to use for storing logs
    |
    */
    'database_connection' => env('LOGBOOK_DB_CONNECTION', null),

    /*
    |--------------------------------------------------------------------------
    | Request Start Logging
    |--------------------------------------------------------------------------
    |
    | Log detailed request start events
    |
    */
    'log_request_start' => env('LOGBOOK_LOG_REQUEST_START', true),

    /*
    |--------------------------------------------------------------------------
    | Token Extraction
    |--------------------------------------------------------------------------
    |
    | Extract user information from tokens
    |
    */
    'extract_user_from_token' => env('LOGBOOK_EXTRACT_USER_FROM_TOKEN', true),

    /*
    |--------------------------------------------------------------------------
    | Token Drivers
    |--------------------------------------------------------------------------
    |
    | Support for different token types
    |
    */
    'token_drivers' => [
        'sanctum' => true,  // Laravel Sanctum support
        'passport' => false, // Laravel Passport support (requires additional setup)
        'custom' => false,   // Custom token validation
    ],

    /*
    |--------------------------------------------------------------------------
    | Async Logging
    |--------------------------------------------------------------------------
    |
    | Queue settings for async logging in production
    |
    */
    'async_logging' => env('LOGBOOK_ASYNC_LOGGING', app()->environment('production')),
    'queue_connection' => env('LOGBOOK_QUEUE_CONNECTION', 'default'),
    'queue_name' => env('LOGBOOK_QUEUE_NAME', 'default'),
];
