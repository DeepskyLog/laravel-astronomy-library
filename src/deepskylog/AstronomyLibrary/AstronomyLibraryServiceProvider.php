<?php

namespace deepskylog\AstronomyLibrary;

use Illuminate\Support\ServiceProvider;

class AstronomyLibraryServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'deepskylog');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'deepskylog');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publish the migration
        $this->publishes(
            [
                __DIR__.'/../../database/migrations/create_deltat_table.php.stub' => database_path(
                    'migrations/'.date('Y_m_d_His', time())
                    .'_create_deltat_table.php'
                ),
            ],
            'migrations'
        );
        $this->publishes(
            [
                __DIR__.'/../../../data/deltat.csv' => database_path(
                    'deltat.csv'
                ),
            ],
            'migrations'
        );

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     */
    public function register()
    {
        // Register the service the package provides.
        $this->app->singleton(
            'AstronomyLibrary',
            function ($app) {
                return new AstronomyLibrary;
            }
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['laravel-astronomy-library'];
    }

    /**
     * Console-specific booting.
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        // $this->publishes(
        //     [
        //         __DIR__ . '/../config/astronomyLibrary.php' => config_path('astronomyLibrary.php'),
        //     ],
        //     'astronomyLibrary.config'
        // );

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/deepskylog'),
        ], 'laravel-astronomy-library.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/deepskylog'),
        ], 'laravel-astronomy-library.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/deepskylog'),
        ], 'laravel-astronomy-library.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
