<?php

namespace deepskylog\AstronomyLibrary\Facades;

use Illuminate\Support\Facades\Facade;

class astronomyLibrary extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \deepskylog\AstronomyLibrary\AstronomyLibrary::class;
    }
}
