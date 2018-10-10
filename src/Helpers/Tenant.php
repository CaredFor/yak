<?php


namespace Benwilkins\Yak\Helpers;


use Illuminate\Support\Facades\DB;

class Tenant implements \Benwilkins\Yak\Contracts\Helpers\Tenant
{
    public static function connect(string $connection)
    {
        DB::setDefaultConnection($connection);
    }

    public static function current()
    {
        DB::getDefaultConnection();
    }
}