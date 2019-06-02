<?php

namespace PWWEB\Artomator\Facades;

use Illuminate\Support\Facades\Facade;

class Artomator extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'artomator';
    }
}
