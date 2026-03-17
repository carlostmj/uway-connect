<?php

declare(strict_types=1);

namespace CarlosTMJ\UwayConnect\Laravel;

use CarlosTMJ\UwayConnect\UwayConnect;
use Illuminate\Support\Facades\Facade;

/**
 * Facade Laravel para acesso rapido ao cliente do SDK.
 */
class UwayConnectFacade extends Facade
{
    /**
     * Retorna o binding do container usado pelo facade.
     */
    protected static function getFacadeAccessor(): string
    {
        return UwayConnect::class;
    }
}
