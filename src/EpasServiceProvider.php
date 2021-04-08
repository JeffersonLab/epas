<?php

namespace Jlab\Epas;

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Jlab\Epas\Console\Commands\UploadPlantItems;
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
        $this->loadRoutes();
        $this->registerCommands();
        $this->registerPolicies();

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
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
     * Publish the packages database migration files.
     * @return void
     */
    protected function publishMigrations(){
        if ($this->app->runningInConsole()) {
            // Export the migration
             $this->publishes([
                    __DIR__ . '/../database/migrations/2021_00_00_000001_create_plant_items_table.php'
                            => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_plant_items_table.php'),
                    __DIR__ . '/../database/migrations/2021_00_00_000002_create_isolation_points_table.php'
                 => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_isolation_points_table.php'),
                ], 'migrations');
            }
    }

    /**
     * Publish the package's configuration files
     * @return void
     */
    protected function publishConfig()
    {
        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__ . '/../config/epas.php' => config_path('epas.php'),
            ], 'config');

        }
    }

    /**
     * Register the Artisan console commands provided by the package.
     * @return void
     */
    protected function registerCommands(){
        $this->commands([
            UploadPlantItems::class
        ]);
    }

    /**
     * Read in our HTTP routes from the appropriate files.
     */
    protected function loadRoutes(){
        // Must include the bindings middleware in order to get
        // route model binding for routes provided via package like so.
        Route::group([
            'middleware' => [SubstituteBindings::class],
            'prefix' => 'api',
            'namespace' => '',
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        });

        Route::group([
            'middleware' => ['web', SubstituteBindings::class],
            'prefix' => '',
            'namespace' => '',
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    /**
     * Register the authorization policy classes
     */
    protected function registerPolicies(){
        Gate::policy(PlantItem::class, PlantItemPolicy::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
//        return ['epas'];
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

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/jlab'),
        ], 'epas.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/jlab'),
        ], 'epas.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/jlab'),
        ], 'epas.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
