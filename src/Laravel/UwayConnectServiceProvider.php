<?php

declare(strict_types=1);

namespace CarlosTMJ\UwayConnect\Laravel;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use CarlosTMJ\UwayConnect\Config;
use CarlosTMJ\UwayConnect\UwayConnect;

class UwayConnectServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/uway-connect.php', 'uway-connect');

        $this->app->singleton(UwayConnect::class, function ($app): UwayConnect {
            $config = $app['config']->get('uway-connect');

            $sdkConfig = new Config(
                (string) ($config['base_url'] ?? 'https://auth.uway.com.br'),
                (string) ($config['client_id'] ?? ''),
                $config['client_secret'] ?? null,
                (string) ($config['redirect_uri'] ?? ''),
                is_array($config['scopes'] ?? null) ? $config['scopes'] : ['openid'],
                (int) ($config['timeout'] ?? 15),
                (bool) ($config['verify_ssl'] ?? true)
            );

            return new UwayConnect($sdkConfig, new Client([
                'base_uri' => rtrim($sdkConfig->baseUrl, '/').'/oauth/',
                'timeout' => $sdkConfig->timeoutSeconds,
                'verify' => $sdkConfig->verifySsl,
            ]));
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/uway-connect.php' => config_path('uway-connect.php'),
        ], 'uway-connect');
    }
}




