<?php

namespace Jlab\Epas;

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Jlab\Epas\Console\Commands\UploadPlantItems;
use Jlab\Epas\Http\Middleware\SetPlantItemRootView;
use Jlab\Epas\Model\PlantItem;
use Jlab\Epas\Policies\PlantItemPolicy;

class EpasServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->setupInertia();

        $router = $this->app->make(Router::class);
        //$router->pushMiddlewareToGroup('web', SetPlantItemRootView::class);

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'jlab-epas');

        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');





        $this->declarePolicies();



        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register package services.
     *
     * @return void
     */
    public function register(): void
    {
        // mergeConfig allows the package to provide default values that the
        // caller need only override selectively
        // @see https://laravel.com/docs/8.x/packages#default-package-configuration
        $this->mergeConfigFrom(__DIR__.'/../config/epas.php', 'epas');

    }


    /**
     * Declare the authorization policy classes
     */
    protected function declarePolicies(){
        Gate::policy(PlantItem::class, PlantItemPolicy::class);
    }


    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        $this->publishConfig();
        $this->publishMigrations();
        $this->publishResources();

        // declare package commands
        $this->commands([
            UploadPlantItems::class
        ]);
    }

    private function setupInertia()
    {
//        Inertia::version(function () {
//            // TODO
//            if (config('app.env') == 'testing') {
//                return;
//            }
//
//            return md5_file(public_path('vendor/jlab-epas/mix-manifest.json'));
//        });
//
//        Inertia::setRootView('jlab-epas::app');
    }

    /**
     * Publish the package's javascript and css assets.
     * @return void
     */
    protected function publishResources(){
        // Export the resources
        $this->publishes([
            __DIR__ . '/../resources' => resource_path('vendor/jlab-epas'),
        ], 'jlab-epas-resources');

        // Publishing assets.
        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/jlab-epas'),
        ], ['jlab-epas', 'jlab-epas-assets']);

    }


    /**
     * Publish the packages database migration files.
     * @return void
     */
    protected function publishMigrations(){

        // Export the migration
        $this->publishes([
            __DIR__ . '/../database/migrations/2021_00_00_000001_create_plant_items_table.php'
            => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_plant_items_table.php'),
            __DIR__ . '/../database/migrations/2021_00_00_000002_create_isolation_points_table.php'
            => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_isolation_points_table.php'),
        ], 'migrations');

    }

    /**
     * Publish the package's configuration files
     * @return void
     */
    protected function publishConfig()
    {
        $this->publishes([
            __DIR__ . '/../config/epas.php' => config_path('epas.php'),
        ], 'config');
    }
}
