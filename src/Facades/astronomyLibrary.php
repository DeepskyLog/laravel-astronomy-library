<?php

namespace deepskylog\AstronomyLibrary\Facades;

use Illuminate\Support\Facades\Facade;

class AstronomyLibrary extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-astronomy-library';
    }
}
