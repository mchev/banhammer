<?php

namespace Mchev\Banhammer\Tests;

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function defineEnvironment($app): void
    {
        // Load the .env file
        $app->useEnvironmentPath(__DIR__.'/..');
        $app->bootstrapWith([LoadEnvironmentVariables::class]);
    }

    protected function getPackageProviders($app): array
    {
        return [
            'Mchev\Banhammer\BanhammerServiceProvider',
        ];
    }

    protected function defineDatabase($app)
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
