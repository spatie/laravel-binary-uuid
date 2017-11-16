<?php

namespace Spatie\Uuid\Test;

use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Uuid\UuidServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp()
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            UuidServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
