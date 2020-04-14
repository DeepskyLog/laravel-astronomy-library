<?php

namespace deepskylog\laravel-astronomy-library;

use Illuminate\Support\ServiceProvider;

class laravel-astronomy-libraryServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'deepskylog');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'deepskylog');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

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
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-astronomy-library.php', 'laravel-astronomy-library');

        // Register the service the package provides.
        $this->app->singleton('laravel-astronomy-library', function ($app) {
            return new laravel-astronomy-library;
        });
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
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/laravel-astronomy-library.php' => config_path('laravel-astronomy-library.php'),
        ], 'laravel-astronomy-library.config');

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
