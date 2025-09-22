# Laravel Logbook

A lightweight Laravel package to record, monitor, and manage API user journeys with a beautiful admin dashboard.

## Installation

Install the package via Composer:

```bash
composer require rahban/laravel-logbook
```

### Publish Configuration and Migrations

Publish the configuration file:

```bash
php artisan vendor:publish --tag=logbook-config
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag=logbook-migrations
php artisan migrate
```

### Publish Views (Optional)

If you want to customize the admin dashboard views:

```bash
php artisan vendor:publish --tag=logbook-views
```

### Publish Assets (Optional)

If you want to customize the frontend assets:

```bash
php artisan vendor:publish --tag=logbook-assets
```

### All-in-One Publishing

You can also publish everything at once:

```bash
php artisan vendor:publish --provider="Rahban\LaravelLogbook\Providers\LogbookServiceProvider"
```

## Quick Setup

### 1. Environment Configuration

Add these variables to your `.env` file:

```env
LOGBOOK_ENABLED=true
LOGBOOK_UI_ENABLED=true
LOGBOOK_UI_PREFIX=logbook
LOGBOOK_USER=admin
LOGBOOK_PASS=your_secure_password
LOGBOOK_RETENTION_DAYS=90
```

### 2. Add Middleware

**Option A: Global Middleware (Recommended)**

Add to `app/Http/Kernel.php`:

```php
protected $middleware = [
    // ...existing middleware...
    \Rahban\LaravelLogbook\Http\Middleware\LogbookMiddleware::class,
];
```

**Option B: Route-specific Middleware**

Apply to specific routes:

```php
// In routes/api.php or routes/web.php
Route::middleware(['logbook'])->group(function () {
    Route::get('/api/users', [UserController::class, 'index']);
    // ...other routes
});
```

### 3. Access Admin Dashboard

Visit: `http://your-app.com/logbook`

Default credentials:

- **Username:** `admin`
- **Password:** `your_secure_password` (from .env)

## Usage Examples

### Custom Event Logging

```php
use Rahban\LaravelLogbook\Facades\Logbook;

// Log user actions
Logbook::event('user.login', [
    'email' => $user->email,
    'ip' => request()->ip(),
], $user->id);

// Log business events
Logbook::event('order.created', [
    'order_id' => $order->id,
    'amount' => $order->total,
    'currency' => 'USD',
], $order->user_id);
```

### Using with User Models

Add the trait to your User model:

```php
use Rahban\LaravelLogbook\Traits\HasLogbook;

class User extends Authenticatable
{
    use HasLogbook;

    // Now you can use:
    // $user->logLogin();
    // $user->logLogout();
    // $user->logAction('profile_updated');
}
```

### Service Injection

```php
use Rahban\LaravelLogbook\Services\LogbookService;

class OrderController extends Controller
{
    public function store(Request $request, LogbookService $logbook)
    {
        $order = Order::create($request->all());

        $logbook->event('order.created', [
            'order_id' => $order->id,
            'amount' => $order->total,
        ], auth()->id());

        return response()->json($order);
    }
}
```

## Artisan Commands

### Cleanup Old Logs

```bash
# Clean logs older than default retention period
php artisan logbook:cleanup

# Clean logs older than specific days
php artisan logbook:cleanup --days=30

# Clean logs by date range
php artisan logbook:cleanup --from=2023-01-01 --to=2023-12-31

# Delete all logs (be careful!)
php artisan logbook:cleanup --all --force
```

### Scheduled Cleanup

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Clean old logs daily at 2 AM
    $schedule->command('logbook:cleanup --force')
             ->daily()
             ->at('02:00');
}
```

## Configuration

The configuration file `config/logbook.php` provides many customization options:

```php
return [
    // Enable/disable logging
    'enabled' => env('LOGBOOK_ENABLED', true),

    // Admin UI settings
    'ui_enabled' => env('LOGBOOK_UI_ENABLED', true),
    'ui_route_prefix' => env('LOGBOOK_UI_PREFIX', 'logbook'),

    // Authentication
    'auth_user' => env('LOGBOOK_USER', 'admin'),
    'auth_pass' => env('LOGBOOK_PASS', 'password'),

    // Data retention
    'retention_days' => env('LOGBOOK_RETENTION_DAYS', 90),

    // Security - fields to mask
    'mask_fields' => [
        'password', 'token', 'secret', 'api_key',
    ],

    // Body size limit (bytes)
    'truncate_body_at' => env('LOGBOOK_TRUNCATE_AT', 10240),

    // Routes to exclude from logging
    'excluded_routes' => [
        'logbook/*',
        'telescope/*',
        '_debugbar/*',
    ],
];
```

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

## Dashboard Features

- 📊 **Overview Statistics**: Request counts, error rates, response times
- 📋 **Request Tracks**: Detailed list of all logged requests with filtering
- 🔍 **Entry Details**: Inspect individual requests with full headers and bodies
- 🧹 **Management Panel**: Cleanup tools and storage statistics

## Security

- Sensitive fields are automatically masked
- Admin panel requires authentication
- Headers containing tokens are sanitized
- CSRF protection on management operations

## Performance Considerations

- **Production**: Logging is asynchronous by default in production
- **Storage**: Configure retention policies to manage database size
- **Indexing**: Database indexes are included for optimal query performance
- **Truncation**: Large request/response bodies are automatically truncated

## Testing

Run the package tests:

```bash
composer test
```

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
