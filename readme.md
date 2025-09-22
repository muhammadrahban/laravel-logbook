# Laravel Logbook

A lightweight Laravel package to record, monitor, and manage API user journeys with a beautiful admin dashboard. Automatically tracks HTTP requests, extracts user information from Bearer tokens, and provides comprehensive logging with a clean web interface.

## ✨ Features

- 🚀 **Automatic Request Logging**: Captures all HTTP requests with response times, status codes, headers, and payloads
- 👤 **Smart User Tracking**: Automatically extracts user IDs from Bearer tokens (Sanctum/Passport support)
- 🎯 **Custom Event Tracking**: Log custom application events with structured data
- 🔒 **Security First**: Automatically masks sensitive fields (passwords, tokens, credit cards, etc.)
- 📊 **Beautiful Admin Dashboard**: Clean, responsive web interface with Bootstrap styling
- 🧹 **Intelligent Data Management**: Built-in cleanup commands with flexible retention policies
- ⚡ **Performance Optimized**: Asynchronous logging in production, database indexing
- 🔍 **Advanced Filtering**: Filter logs by method, status, user, date range, endpoints, and more
- 📱 **Mobile Friendly**: Responsive dashboard design works on all devices
- 🎨 **Highly Customizable**: Extensive configuration options for different environments

## 📋 Requirements

- PHP 8.0 or higher
- Laravel 9.0, 10.0, or 11.0
- MySQL, PostgreSQL, or SQLite database

## 🚀 Installation

### 1. Install the Package

```bash
composer require rahban/laravel-logbook
```

### 2. Publish Configuration and Migrations

**Publish the configuration file:**

```bash
php artisan vendor:publish --tag=logbook-config
```

**Publish and run the migrations:**

```bash
php artisan vendor:publish --tag=logbook-migrations
php artisan migrate
```

### 3. Optional: Publish Views and Assets

**Customize the admin dashboard views:**

```bash
php artisan vendor:publish --tag=logbook-views
```

**Publish frontend assets:**

```bash
php artisan vendor:publish --tag=logbook-assets
```

**All-in-one publishing:**

```bash
php artisan vendor:publish --provider="Rahban\LaravelLogbook\Providers\LogbookServiceProvider"
```

## ⚙️ Configuration

### Environment Variables

Add these variables to your `.env` file:

```env
LOGBOOK_ENABLED=true
LOGBOOK_UI_ENABLED=true
LOGBOOK_UI_PREFIX=logbook
LOGBOOK_USER=admin
LOGBOOK_PASS=your_secure_password
LOGBOOK_RETENTION_DAYS=90
LOGBOOK_LOG_REQUEST_START=true
LOGBOOK_EXTRACT_USER_FROM_TOKEN=true
```

### Add Middleware

**Option A: Global Middleware (Recommended for APIs)**

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
    Route::post('/api/orders', [OrderController::class, 'store']);
    // ...other routes
});
```

## 🎯 Usage

### Automatic Request Logging

Once the middleware is installed, **all HTTP requests are automatically logged** with:

- Request method, URL, headers, and payload
- Response status, headers, and body
- Response time in milliseconds
- User ID automatically extracted from Bearer tokens
- IP address and user agent
- Route information and middleware stack

### Bearer Token & User Tracking

The package automatically extracts user IDs from Bearer tokens:

```php
// Your API request with Authorization header:
// Authorization: Bearer abc123def456...

// Logbook automatically:
// 1. Extracts user ID from the token (if using Sanctum/Passport)
// 2. Logs the request with user association
// 3. Masks the token for security (shows: "abc123de***")
```

### Custom Event Logging

Log custom business events throughout your application:

```php
use Rahban\LaravelLogbook\Facades\Logbook;

// Log user actions
Logbook::event('user.login', [
    'email' => $user->email,
    'ip' => request()->ip(),
    'login_method' => 'email',
], $user->id);

// Log business events
Logbook::event('order.created', [
    'order_id' => $order->id,
    'amount' => $order->total,
    'currency' => 'USD',
    'payment_method' => 'credit_card',
], $order->user_id);

// Log system events
Logbook::event('email.sent', [
    'template' => 'welcome',
    'recipient' => $user->email,
    'status' => 'delivered',
]);
```

### Using with User Models

Add the trait to your User model for convenient logging:

```php
use Rahban\LaravelLogbook\Traits\HasLogbook;

class User extends Authenticatable
{
    use HasLogbook;

    // Now you can use:
    public function login()
    {
        $this->logLogin(['device' => 'mobile']);
        // Automatically logs user.login event
    }

    public function updateProfile()
    {
        $this->logAction('profile_updated', [
            'fields' => ['name', 'email']
        ]);
    }

    public function logout()
    {
        $this->logLogout();
        // Automatically logs user.logout event
    }
}
```

### Service Injection

Use dependency injection in your controllers:

```php
use Rahban\LaravelLogbook\Services\LogbookService;

class OrderController extends Controller
{
    public function store(Request $request, LogbookService $logbook)
    {
        $order = Order::create($request->all());

        // Log the business event
        $logbook->event('order.created', [
            'order_id' => $order->id,
            'amount' => $order->total,
            'items_count' => $order->items->count(),
        ], auth()->id());

        return response()->json($order);
    }
}
```

## 📊 Admin Dashboard

### Access the Dashboard

Visit: `http://your-app.com/logbook`

**Default credentials:**

- Username: `admin`
- Password: `your_secure_password` (from .env)

### Dashboard Features

#### 📈 **Overview Page**

- Total requests and custom events count
- Error rate percentage and average response time
- Status code distribution (2xx, 3xx, 4xx, 5xx)
- Recent activity timeline
- Top endpoints by request count

#### 📋 **Request Tracks**

- Paginated list of all logged requests
- **Advanced filtering:**
  - HTTP method (GET, POST, PUT, DELETE)
  - Status code ranges
  - User ID
  - Date range picker
  - Endpoint search
  - Response time ranges

#### 🔍 **Entry Details**

- Complete request/response inspection
- Formatted JSON viewer
- Headers analysis
- Performance metrics
- User information (if authenticated)
- Route and middleware details

#### 🧹 **Management Panel**

- **Storage statistics:** Total entries, storage size, old entries count
- **Cleanup operations:**
  - Clean by days (older than X days)
  - Clean by date range
  - Clean all data (with confirmation)
- **Real-time statistics:** Error rates, performance metrics

## 🛠️ Artisan Commands

### Cleanup Commands

```bash
# Clean logs older than default retention period (90 days)
php artisan logbook:cleanup

# Clean logs older than specific days
php artisan logbook:cleanup --days=30

# Clean logs by date range
php artisan logbook:cleanup --from=2024-01-01 --to=2024-12-31

# Delete all logs (use with caution!)
php artisan logbook:cleanup --all --force

# Preview what would be deleted (dry run)
php artisan logbook:cleanup --days=90 --dry-run
```

### Scheduled Cleanup

Add automatic cleanup to your `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Clean old logs daily at 2 AM
    $schedule->command('logbook:cleanup --force')
             ->daily()
             ->at('02:00');

    // Weekly cleanup with custom retention
    $schedule->command('logbook:cleanup --days=30 --force')
             ->weekly()
             ->sundays()
             ->at('03:00');
}
```

## ⚙️ Advanced Configuration

The configuration file `config/logbook.php` provides extensive customization:

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

    // Security - fields to automatically mask
    'mask_fields' => [
        'password', 'password_confirmation', 'token', 'api_key',
        'secret', 'access_token', 'refresh_token', 'credit_card',
        'ssn', 'cvv',
    ],

    // Body size limit (10KB default)
    'truncate_body_at' => env('LOGBOOK_TRUNCATE_AT', 10240),

    // Routes to exclude from logging
    'excluded_routes' => [
        'logbook/*', 'telescope/*', '_debugbar/*', 'health-check',
    ],

    // Token extraction settings
    'extract_user_from_token' => env('LOGBOOK_EXTRACT_USER_FROM_TOKEN', true),
    'log_request_start' => env('LOGBOOK_LOG_REQUEST_START', true),

    // Performance settings
    'async_logging' => env('LOGBOOK_ASYNC_LOGGING', app()->environment('production')),
    'queue_connection' => env('LOGBOOK_QUEUE_CONNECTION', 'default'),
];
```

## 🔒 Security Features

### Automatic Data Masking

Sensitive fields are automatically masked in request/response bodies:

- Passwords and password confirmations
- API keys and access tokens
- Credit card numbers and CVV codes
- Social security numbers
- Custom sensitive fields (configurable)

### Header Sanitization

Authorization headers and cookies are automatically sanitized:

```php
// Original: Authorization: Bearer abc123def456ghi789
// Logged:   Authorization: Bearer abc123de***
```

### Admin Panel Protection

- Basic HTTP authentication
- CSRF protection on all management operations
- Session-based authentication
- Configurable credentials via environment variables

## 📈 Performance Considerations

### Production Optimizations

- **Asynchronous logging** in production environments
- **Database indexing** for optimal query performance
- **Body truncation** to prevent large data storage
- **Configurable retention policies** for automated cleanup

### Memory Management

- Efficient data processing with minimal memory footprint
- Stream-based body processing for large requests
- Automatic garbage collection of old entries

### Scalability

- Supports horizontal scaling with shared database
- Queue-based processing for high-traffic applications
- Configurable database connections for separation

## 🧪 Testing

### Run Package Tests

```bash
# Run all tests
composer test

# Run with coverage
composer test-coverage

# Run specific test
vendor/bin/phpunit --filter=test_middleware_logs_requests
```

### Test Coverage

The package includes comprehensive tests covering:

- Custom event logging
- Request/response logging
- User ID extraction from tokens
- Data masking and security
- Cleanup commands
- Statistics calculation

## 🔧 Troubleshooting

### Common Issues

**SQLite Driver Missing (for testing):**

```bash
sudo apt-get install php8.3-sqlite3
```

**Permission Issues:**

```bash
php artisan config:cache
php artisan route:cache
```

**Large Log Files:**

```bash
php artisan logbook:cleanup --days=7 --force
```

### Debug Mode

Enable debug logging in your `.env`:

```env
LOGBOOK_ENABLED=true
LOG_LEVEL=debug
```

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guidelines](CONTRIBUTING.md) for details.

### Development Setup

1. Fork the repository
2. Clone your fork: `git clone https://github.com/yourusername/laravel-logbook.git`
3. Install dependencies: `composer install`
4. Run tests: `composer test`
5. Create a feature branch: `git checkout -b feature/amazing-feature`
6. Make your changes and add tests
7. Run tests: `composer test`
8. Commit changes: `git commit -m 'Add amazing feature'`
9. Push to branch: `git push origin feature/amazing-feature`
10. Open a Pull Request

## 📜 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Support

- 🐛 **Bug Reports:** [Create an issue](https://github.com/rahban/laravel-logbook/issues)
- 💡 **Feature Requests:** [Create an issue](https://github.com/rahban/laravel-logbook/issues)
- 📖 **Documentation:** Check our [Wiki](https://github.com/rahban/laravel-logbook/wiki)
- ⭐ **Star the repository** if you find it useful
- 🗣️ **Share with fellow developers**

## 📞 Contact

- **Author:** Muhammad Rahban
- **Email:** muhammadrahban@example.com
- **GitHub:** [@rahban](https://github.com/rahban)
- **LinkedIn:** [Muhammad Rahban](https://linkedin.com/in/rahban)

---

<div align="center">

**Made with ❤️ by [Muhammad Rahban](https://github.com/rahban)**

_Laravel Logbook - Track Every Journey_

</div>
