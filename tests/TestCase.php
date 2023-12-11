<?php

namespace Empuxa\LocaleViaApi\Tests;

use Empuxa\LocaleViaApi\LocaleViaApiServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            LocaleViaApiServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
