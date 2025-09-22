<?php

namespace Rahban\LaravelLogbook\Facades;

use Illuminate\Support\Facades\Facade;

class Logbook extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'logbook';
    }
}
