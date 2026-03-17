<?php

declare(strict_types=1);

namespace CarlosTMJ\UwayConnect\Tests\Laravel;

use CarlosTMJ\UwayConnect\Laravel\UwayConnectFacade;
use CarlosTMJ\UwayConnect\Tests\TestCase;
use CarlosTMJ\UwayConnect\UwayConnect;

final class UwayConnectServiceProviderTest extends TestCase
{
    public function testPackageResolvesThroughContainerAndFacade(): void
    {
        $instance = $this->app->make(UwayConnect::class);

        $this->assertInstanceOf(UwayConnect::class, $instance);

        $request = UwayConnectFacade::createAuthorizationRequest();

        $this->assertSame(['basic', 'openid'], $request->scopes);
        $this->assertStringContainsString('client_id=client-test', $request->url);
    }
}
