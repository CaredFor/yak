<?php


namespace Benwilkins\Yak\Events;
use Benwilkins\Yak\Contracts\Helpers\Tenant;
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
        resolve(Tenant::class)->connect($this->connectionName);
        $this->wakeUp();
    }
}