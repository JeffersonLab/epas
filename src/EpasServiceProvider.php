<?php

namespace Jlab\Epas;

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Jlab\Epas\Console\Commands\UploadPlantItems;
use Jlab\Epas\Model\PlantItem;
use Jlab\Epas\Policies\PlantItemPolicy;
use Jlab\Epas\Utility\PlantItemUtility;

class EpasServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'jlab');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'jlab');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->publishMigrations();
        $this->registerCommands();
        $this->loadRoutes();
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
        $this->mergeConfigFrom(__DIR__.'/../config/epas.php', 'epas');

//        // Register the service the package provides.
//        $this->app->singleton('epas', function ($app) {
//            return new Epas;
//        });
    }

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
        // route model binding in a package like this.
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
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/epas.php' => config_path('epas.php'),
        ], 'epas.config');

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
