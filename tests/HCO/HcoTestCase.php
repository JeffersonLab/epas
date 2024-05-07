<?php

namespace Jlab\Epas\Tests\HCO;

use Orchestra\Testbench\TestCase;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;

class HcoTestCase extends TestCase {


    protected function getEnvironmentSetUp($app)
    {
        // make sure, our .env.testing file is loaded
        $app->useEnvironmentPath(__DIR__.'/../..');
        $app->bootstrapWith([LoadEnvironmentVariables::class]);
        parent::getEnvironmentSetUp($app);

        $app->register(\Yajra\Oci8\Oci8ServiceProvider::class);

        // We need and presume to have the docker oracle available
        // for this test.
        $app['config']->set('database.default', 'oracle');
        $app['config']->set('database.connections.oracle', [
            'driver' => 'oracle',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '1521'),
            'database' => env('DB_DATABASE', 'xepdb1'),
            'service_name' => env('DB_SERVICE_NAME', 'xepdb1'),
            'username' => env('DB_USERNAME', ''),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'AL32UTF8',
            'prefix' => '',
        ]);

    }



}
