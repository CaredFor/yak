<?php


namespace Benwilkins\Yak\Contracts\Helpers;


interface Tenant
{
    public static function connect(string $connection);

    public static function current();
}