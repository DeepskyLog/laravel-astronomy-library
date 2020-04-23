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
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

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
        $this->publishes(
            [
                __DIR__ . '/../config/astronomyLibrary.php' => config_path('astronomyLibrary.php'),
            ],
            'astronomyLibrary.config'
        );

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
