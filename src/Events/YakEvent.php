<?php


namespace Benwilkins\Yak\Events;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;


abstract class YakEvent
{
    use SerializesModels {
        __wakeup as wakeUp;
    }

    public $connectionName;

    public function __wakeup()
    {
        DB::setDefaultConnection($this->connectionName);
        $this->wakeUp();
    }
}