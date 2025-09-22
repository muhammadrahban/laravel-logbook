# Laravel Logbook

A lightweight Laravel package to record, monitor, and manage API user journeys with a beautiful admin dashboard.

## Features

- 🚀 **Automatic Request Logging**: Captures all HTTP requests with response times, status codes, and headers
- 🎯 **Custom Event Tracking**: Log custom application events with structured data
- 🔒 **Data Security**: Automatically masks sensitive fields (passwords, tokens, etc.)
- 📊 **Admin Dashboard**: Beautiful web interface to view logs, statistics, and analytics
- 🧹 **Data Management**: Built-in cleanup commands with flexible retention policies
- ⚡ **Performance Optimized**: Asynchronous logging in production environments
- 🔍 **Advanced Filtering**: Filter logs by method, status, user, date range, and endpoints
- 📱 **Responsive UI**: Mobile-friendly dashboard design
- 🎨 **Customizable**: Configurable settings for different environments

## Installation

Install the package via Composer:

```bash
composer require rahban/laravel-logbook
```

### Laravel Auto-Discovery

The package will automatically register itself. If you're using Laravel 5.5+, skip the provider registration.

### Manual Registration (Laravel 5.4 and below)

Add the service provider to your `config/app.php`:

```php
'providers' => [
    // ...
    Rahban\LaravelLogbook\Providers\LogbookServiceProvider::class,
],

'aliases' => [
    // ...
    'Logbook' => Rahban\LaravelLogbook\Facades\Logbook::class,
],
```

### Publish and Run Migrations

```bash
# Publish the configuration file
php artisan vendor:publish --tag=logbook-config

# Publish the migration files
php artisan vendor:publish --tag=logbook-migrations

# Run the migrations
php artisan migrate
```

## Configuration

The configuration file will be published to `config/logbook.php`. Here are the key settings:

```php
return [
    // Enable/disable logging
    'enabled' => env('LOGBOOK_ENABLED', true),

    // Admin UI settings
    'ui_enabled' => env('LOGBOOK_UI_ENABLED', true),
    'ui_route_prefix' => env('LOGBOOK_UI_PREFIX', 'logbook'),

    // Authentication for admin panel
    'auth_user' => env('LOGBOOK_USER', 'admin'),
    'auth_pass' => env('LOGBOOK_PASS', 'password'),

    // Data retention
    'retention_days' => env('LOGBOOK_RETENTION_DAYS', 90),

    // Security settings
    'mask_fields' => ['password', 'token', 'secret'],
    'truncate_body_at' => env('LOGBOOK_TRUNCATE_AT', 10240),
];
```

### Environment Variables

Add these to your `.env` file:

```env
LOGBOOK_ENABLED=true
LOGBOOK_UI_ENABLED=true
LOGBOOK_UI_PREFIX=logbook
LOGBOOK_USER=admin
LOGBOOK_PASS=secure_password
LOGBOOK_RETENTION_DAYS=90
```

## Usage

### Automatic Request Logging

Add the middleware to your routes or globally:

```php
// In routes/api.php
Route::middleware(['logbook'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
});

// Or globally in app/Http/Kernel.php
protected $middleware = [
    // ...
    \Rahban\LaravelLogbook\Http\Middleware\LogbookMiddleware::class,
];
```

### Custom Event Logging

Log custom events anywhere in your application:

```php
use Rahban\LaravelLogbook\Facades\Logbook;

// Basic event logging
Logbook::event('user.login', [
    'email' => 'user@example.com',
    'ip' => request()->ip(),
], auth()->id());

// Log user actions
Logbook::event('order.created', [
    'order_id' => $order->id,
    'amount' => $order->total,
    'currency' => 'USD',
], $order->user_id);
```

### Using the HasLogbook Trait

Add the trait to your User model for convenient logging:

```php
use Rahban\LaravelLogbook\Traits\HasLogbook;

class User extends Authenticatable
{
    use HasLogbook;

    // Now you can use:
    // $user->logLogin();
    // $user->logLogout();
    // $user->logAction('profile_updated', ['field' => 'email']);
    // $user->logEvent('custom.event', ['data' => 'value']);
}
```

### Service Usage

```php
use Rahban\LaravelLogbook\Services\LogbookService;

class OrderController extends Controller
{
    public function store(Request $request, LogbookService $logbook)
    {
        $order = Order::create($request->all());

        // Log the event
        $logbook->event('order.created', [
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'amount' => $order->total,
        ], auth()->id());

        return response()->json($order);
    }
}
```

## Admin Dashboard

Access the admin dashboard at: `http://yourapp.com/logbook`

Default credentials:

- Username: `admin`
- Password: `password`

### Dashboard Features

- 📊 **Overview Statistics**: Request counts, error rates, response times
- 📋 **Request Tracks**: Detailed list of all logged requests with filtering
- 🔍 **Entry Details**: Inspect individual requests with full headers and bodies
- 🧹 **Management Panel**: Cleanup tools and storage statistics

## Artisan Commands

### Cleanup Old Logs

```bash
# Clean logs older than 90 days (default retention)
php artisan logbook:cleanup

# Clean logs older than specific days
php artisan logbook:cleanup --days=30

# Clean logs by date range
php artisan logbook:cleanup --from=2023-01-01 --to=2023-12-31

# Delete all logs (use with caution)
php artisan logbook:cleanup --all --force
```

### Schedule Automatic Cleanup

Add to your `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Clean logs older than retention period daily
    $schedule->command('logbook:cleanup --force')
             ->daily()
             ->at('02:00');
}
```

## Advanced Configuration

### Excluding Routes

Exclude specific routes from logging:

```php
'excluded_routes' => [
    'logbook/*',
    'telescope/*',
    'horizon/*',
    '_debugbar/*',
    'health-check',
],
```

### Custom Database Connection

Use a separate database connection:

```php
'database_connection' => 'logs',
```

### Customizing Data Masking

Add fields to mask in logged data:

```php
'mask_fields' => [
    'password',
    'password_confirmation',
    'token',
    'api_key',
    'secret',
    'credit_card',
    'ssn',
    'access_token',
],
```

## Testing

Run the package tests:

```bash
composer test
```

## Performance Considerations

- **Production**: Logging is asynchronous by default in production
- **Storage**: Configure retention policies to manage database size
- **Indexing**: Database indexes are included for optimal query performance
- **Truncation**: Large request/response bodies are automatically truncated

## Security

- Sensitive fields are automatically masked
- Admin panel requires authentication
- Headers containing tokens are sanitized
- CSRF protection on management operations

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Support

- Create an issue for bug reports
- Star the repository if you find it useful
- Share with your fellow developers

---

Made with ❤️ by [Muhammad Rahban](https://github.com/rahban)
