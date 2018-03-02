<?php

namespace Benwilkins\Yak\Facades;

use Illuminate\Support\Facades\Facade;

class Yak extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'yak';
    }
}