<?php

namespace Mchev\Banhammer;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Mchev\Banhammer\Commands\ClearBans;
use Mchev\Banhammer\Commands\DeleteExpired;
use Mchev\Banhammer\Middleware\AuthBanned;
use Mchev\Banhammer\Middleware\BlockByCountry;
use Mchev\Banhammer\Middleware\IPBanned;
use Mchev\Banhammer\Middleware\LogoutBanned;
use Mchev\Banhammer\Observers\BanObserver;

class BanhammerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        config('ban.model')::observe(BanObserver::class);

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('auth.banned', AuthBanned::class);
        $router->aliasMiddleware('ip.banned', IPBanned::class);
        $router->aliasMiddleware('logout.banned', LogoutBanned::class);

        if (config('ban.block_by_country')) {
            $router->pushMiddlewareToGroup('web', BlockByCountry::class);
        }

        if ($this->app->runningInConsole()) {
            // Publishing the config.
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('ban.php'),
            ], 'config');

            // Publishing migrations
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'migrations');

            // Registering package commands.
            $this->commands([
                ClearBans::class,
                DeleteExpired::class,
            ]);
        }

        if (config('ban.scheduler_enabled', true)) {
            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);
                $periodicity = config('ban.scheduler_periodicity', 'everyMinute');

                $scheduledCommand = $schedule->command('banhammer:unban');

                // Dynamically call the periodicity method if it exists
                if (method_exists($scheduledCommand, $periodicity)) {
                    $scheduledCommand->{$periodicity}();
                } else {
                    // Fallback to everyMinute if method doesn't exist
                    $scheduledCommand->everyMinute();
                }
            });
        }

    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'ban');
    }
}
