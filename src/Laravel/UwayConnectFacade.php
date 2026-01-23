<?php

declare(strict_types=1);

namespace CarlosTMJ\UwayConnect\Laravel;

use Illuminate\Support\Facades\Facade;
use CarlosTMJ\UwayConnect\UwayConnect;

class UwayConnectFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return UwayConnect::class;
    }
}




