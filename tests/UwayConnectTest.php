<?php

declare(strict_types=1);

namespace CarlosTMJ\UwayConnect\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use CarlosTMJ\UwayConnect\Config;
use CarlosTMJ\UwayConnect\DiscoveryDocument;
use CarlosTMJ\UwayConnect\UwayConnect;

final class UwayConnectTest extends TestCase
{
    public function testAuthorizationRequestUsesAuthDefaults(): void
    {
        $sdk = new UwayConnect(new Config(
            baseUrl: 'https://auth.example.com',
            clientId: 'client-123',
            clientSecret: null,
            redirectUri: 'https://app.example.com/callback'
        ));

        $request = $sdk->createAuthorizationRequest();

        $this->assertSame(['basic', 'openid'], $request->scopes);
        $this->assertStringContainsString('scope=basic+openid', $request->url);
        $this->assertStringContainsString('code_challenge=', $request->url);
    }

    public function testClientCredentialsTokenSupportsWrappedAuthResponse(): void
    {
        $sdk = new UwayConnect(
            new Config(
                baseUrl: 'https://auth.example.com',
                clientId: 'client-123',
                clientSecret: 'secret-123',
                redirectUri: 'https://app.example.com/callback'
            ),
            $this->mockedClient([
                new Response(200, [], json_encode([
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'OK',
                    'data' => [
                        'access_token' => 'access-123',
                        'refresh_token' => null,
                        'expires_in' => 3600,
                        'token_type' => 'Bearer',
                        'scope' => 'basic openid',
                    ],
                ], JSON_THROW_ON_ERROR)),
            ])
        );

        $tokens = $sdk->clientCredentialsToken();

        $this->assertSame('access-123', $tokens->accessToken);
        $this->assertSame('basic openid', $tokens->scope);
    }

    public function testDiscoveryReadsAuthSpecificEndpoints(): void
    {
        $sdk = new UwayConnect(
            new Config(
                baseUrl: 'https://auth.example.com',
                clientId: 'client-123',
                clientSecret: null,
                redirectUri: 'https://app.example.com/callback'
            ),
            $this->mockedClient([
                new Response(200, [], json_encode([
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'OK',
                    'data' => [
                        'issuer' => 'https://auth.example.com',
                        'authorization_endpoint' => 'https://auth.example.com/oauth/authorize',
                        'token_endpoint' => 'https://auth.example.com/oauth/token',
                        'userinfo_endpoint' => 'https://auth.example.com/oauth/userinfo',
                        'uway_account_center_endpoint' => 'https://auth.example.com/account',
                        'uway_account_profile_endpoint' => 'https://auth.example.com/account/profile',
                        'uway_account_security_endpoint' => 'https://auth.example.com/account/security',
                        'uway_account_sessions_endpoint' => 'https://auth.example.com/account/security/sessions',
                        'jwks_uri' => 'https://auth.example.com/.well-known/jwks.json',
                        'scopes_supported' => ['openid', 'basic', 'profile', 'phone', 'document'],
                        'grant_types_supported' => ['authorization_code', 'refresh_token', 'client_credentials'],
                    ],
                ], JSON_THROW_ON_ERROR)),
            ])
        );

        $document = $sdk->discovery();

        $this->assertInstanceOf(DiscoveryDocument::class, $document);
        $this->assertSame('https://auth.example.com/account', $document->accountCenterEndpoint);
        $this->assertContains('client_credentials', $document->grantTypesSupported);
        $this->assertContains('document', $document->scopesSupported);
    }

    /**
     * @param array<int, Response> $responses
     */
    private function mockedClient(array $responses): Client
    {
        $handler = new MockHandler($responses);

        return new Client([
            'handler' => HandlerStack::create($handler),
            'base_uri' => 'https://auth.example.com/oauth/',
        ]);
    }
}
