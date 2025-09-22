<?php

namespace Rahban\LaravelLogbook\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Rahban\LaravelLogbook\Http\Middleware\LogbookMiddleware;
use Rahban\LaravelLogbook\Http\Middleware\LogbookAuthMiddleware;
use Rahban\LaravelLogbook\Services\LogbookService;
use Rahban\LaravelLogbook\Console\Commands\LogbookCleanupCommand;

class LogbookServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/logbook.php',
            'logbook'
        );

        $this->app->singleton('logbook', function () {
            return new LogbookService();
        });

        $this->commands([
            LogbookCleanupCommand::class,
        ]);
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'logbook');

        $this->publishes([
            __DIR__ . '/../Config/logbook.php' => config_path('logbook.php'),
        ], 'logbook-config');

        $this->publishes([
            __DIR__ . '/../Database/Migrations' => database_path('migrations'),
        ], 'logbook-migrations');

        $this->publishes([
            __DIR__ . '/../Resources/assets' => public_path('vendor/logbook'),
        ], 'logbook-assets');

        $this->publishes([
            __DIR__ . '/../Resources/views' => resource_path('views/vendor/logbook'),
        ], 'logbook-views');

        // Register middleware
        $router = $this->app['router'];
        $router->aliasMiddleware('logbook', LogbookMiddleware::class);
        $router->aliasMiddleware('logbook.auth', LogbookAuthMiddleware::class);

        // Load routes
        $this->loadRoutes();
    }

    protected function loadRoutes()
    {
        if (config('logbook.ui_enabled', true)) {
            Route::middleware(['web'])
                ->prefix(config('logbook.ui_route_prefix', 'logbook'))
                ->namespace('Rahban\LaravelLogbook\Http\Controllers')
                ->group(__DIR__ . '/../routes/web.php');
        }
    }
}
