<?php

namespace Mchev\Banhammer;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Mchev\Banhammer\Middleware\AuthenticateBanned;
use Mchev\Banhammer\Models\Ban;
use Mchev\Banhammer\Observers\BanObserver;

class BanhammerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Ban::observe(BanObserver::class);

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('auth.banned', AuthenticateBanned::class);

        if ($this->app->runningInConsole()) {

            // Publishing the config.
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('ban.php'),
            ], 'config');

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'ban');

        // Register the main class to use with the facade
        $this->app->bind('bans-for-laravel', function ($app) {
            return new Banhammer();
        });
    }
}
