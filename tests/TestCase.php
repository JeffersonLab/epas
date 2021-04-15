<?php

namespace Jlab\Epas\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\ServiceProvider;
use Jlab\Epas\EpasServiceProvider;
use Jlab\Epas\Facades\Epas;
use Jlab\LaravelUtilities\PackageServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->artisan('migrate', ['--database' => 'testbench'])->run();

        $pathToFactories = __DIR__ .'/../database/factories';
        $this->withFactories($pathToFactories);


    }

    protected function getPackageProviders($app)
    {
        return
            [
                PackageServiceProvider::class,
                EpasServiceProvider::class,
                ServiceProvider::class,
            ];
    }

    protected function getEnvironmentSetUp($app)
    {

        // app.key is needed for creating hashed passwords.
        $app['config']->set('app.key','base64:Ubu0Hrq9F2uecDIVK8sQdqNfs/PlpP4a7JpPXomBav0=');

        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

    }
}
