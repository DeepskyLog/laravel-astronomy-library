<?php

namespace deepskylog\AstronomyLibrary;

use Illuminate\Support\ServiceProvider;
use deepskylog\AstronomyLibrary\Commands\UpdateDeltaTTable;
use deepskylog\AstronomyLibrary\Commands\UpdateOrbitalElements;

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
                __DIR__ . '/../../database/migrations/create_deltat_table.php.stub' => database_path(
                    'migrations/' . date('Y_m_d_His', time())
                    . '_create_deltat_table.php'
                ),
            ],
            'migrations'
        );
        $this->publishes(
            [
                __DIR__ . '/../../../data/deltat.csv' => database_path(
                    'deltat.csv'
                ),
            ],
            'migrations'
        );
        $this->publishes(
            [
                __DIR__ . '/../../database/migrations/create_comets_orbital_elements_table.php.stub' => database_path(
                    'migrations/' . date('Y_m_d_His', time())
                    . '_create_comets_orbital_elements_table.php'
                ),
            ],
            'migrations'
        );
        $this->publishes(
            [
                __DIR__ . '/../../database/migrations/create_asteroids_orbital_elements_table.php.stub' => database_path(
                    'migrations/' . date('Y_m_d_His', time())
                    . '_create_asteroids_orbital_elements_table.php'
                ),
            ],
            'migrations'
        );
        $this->publishes(
            [
                __DIR__ . '/../../database/migrations/create_constellation_boundaries_table.php.stub' => database_path(
                    'migrations/' . date('Y_m_d_His', time())
                    . '_create_constellation_boundaries_table.php'
                ),
            ],
            'migrations'
        );
        $this->publishes(
            [
                __DIR__ . '/../../../data/conlines.csv' => database_path(
                    'conlines.csv'
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
        $this->app->singleton(
            'deepskylog.AstronomyLibrary.console.kernel',
            function ($app) {
                $dispatcher = $app->make(
                    \Illuminate\Contracts\Events\Dispatcher::class
                );

                return new \deepskylog\AstronomyLibrary\Console\Kernel(
                    $app,
                    $dispatcher
                );
            }
        );

        $this->app->make('deepskylog.AstronomyLibrary.console.kernel');
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
        /*$this->publishes(
            [
                __DIR__ . '/../resources/views' => base_path('resources/views/vendor/deepskylog'),
            ],
            'laravel-astronomy-library.views'
        );*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/deepskylog'),
        ], 'laravel-astronomy-library.views');*/

        // Registering package commands.
        $this->commands([UpdateDeltaTTable::class]);
        $this->commands([UpdateOrbitalElements::class]);
    }
}
