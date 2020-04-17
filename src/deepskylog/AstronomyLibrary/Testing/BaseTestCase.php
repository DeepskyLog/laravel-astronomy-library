<?php

namespace deepskylog\AstronomyLibrary\Testing;

use deepskylog\AstronomyLibrary\AstronomyLibraryServiceProvider;
use Illuminate\Foundation\Testing\TestCase;

class BaseTestCase extends TestCase
{
    /**
     * Base app path.
     *
     * @var string
     */
    protected $appPath;

    /**
     * Instantiates the laravel environment.
     *
     * @return mixed
     */
    public function createApplication()
    {
        // relative path in package folder
        if (! $this->appPath) {
            return;
        }

        $app = require $this->appPath;
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        $app->register(AstronomyLibraryServiceProvider::class);

        return $app;
    }
}
