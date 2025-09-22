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
        'credit_card',
        'ssn',
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
];
