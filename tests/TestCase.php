<?php

namespace Veltrix\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Veltrix\Auth\Database\AdminTablesSeeder;
use Veltrix\Facades\Veltrix;
use Veltrix\VeltrixServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            VeltrixServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Veltrix' => \Veltrix\Facades\Veltrix::class,
            'Admin'   => \Encore\Admin\Facades\Admin::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('app.key', 'base64:6Cu/ozMDhuCQha1v+gV1g2Xw+jJ7BfA21f+3pG8Q1g0=');
        $app['config']->set('veltrix.directory', sys_get_temp_dir() . '/veltrix_test_admin');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function setUp(): void
    {
        parent::setUp();

        AdminTablesSeeder::$defaultUsername = 'admin';
        AdminTablesSeeder::$defaultPassword = 'admin';
        (new AdminTablesSeeder())->run();
    }
}
