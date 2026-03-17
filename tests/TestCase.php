<?php

declare(strict_types=1);

namespace CarlosTMJ\UwayConnect\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use CarlosTMJ\UwayConnect\Laravel\UwayConnectServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [UwayConnectServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('uway-connect.base_url', 'https://auth.example.com');
        $app['config']->set('uway-connect.client_id', 'client-test');
        $app['config']->set('uway-connect.client_secret', 'secret-test');
        $app['config']->set('uway-connect.redirect_uri', 'https://app.example.com/callback');
        $app['config']->set('uway-connect.scopes', ['basic', 'openid']);
        $app['config']->set('uway-connect.verify_ssl', false);
    }
}
