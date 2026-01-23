<?php

declare(strict_types=1);

namespace CarlosTMJ\UwayConnect;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use CarlosTMJ\UwayConnect\Support\Pkce;
use CarlosTMJ\UwayConnect\Support\State;

final class UwayConnect
{
    private ClientInterface $http;

    public function __construct(
        private readonly Config $config,
        ?ClientInterface $http = null,
        private readonly ?LoggerInterface $logger = null
    ) {
        $this->http = $http ?? new Client([
            'base_uri' => rtrim($this->config->baseUrl, '/').'/'.ltrim('oauth/', '/'),
            'timeout' => $this->config->timeoutSeconds,
            'verify' => $this->config->verifySsl,
        ]);
    }

    public function newPkcePair(int $verifierLength = 64): array
    {
        $verifier = Pkce::generateVerifier($verifierLength);

        return [
            'verifier' => $verifier,
            'challenge' => Pkce::challenge($verifier),
            'method' => 'S256',
        ];
    }

    /**
     * @param array<int, string> $scopes
     * @param array<string, string> $extras
     */
    public function createAuthorizationRequest(
        array $scopes = [],
        array $extras = [],
        int $verifierLength = 64
    ): AuthRequest {
        $state = State::generate();
        $pkce = $this->newPkcePair($verifierLength);
        $url = $this->authorizationUrl(
            state: $state,
            scopes: $scopes,
            codeChallenge: $pkce['challenge'],
            codeChallengeMethod: $pkce['method'],
            extras: $extras
        );

        return new AuthRequest(
            $url,
            $state,
            $pkce['verifier'],
            $pkce['challenge'],
            $pkce['method'],
            $scopes !== [] ? $scopes : $this->config->defaultScopes,
            $extras
        );
    }

    /**
     * @param array<int, string> $scopes
     * @param array<string, string> $extras
     */
    public function createSignupRequest(
        array $scopes = [],
        array $extras = [],
        int $verifierLength = 64
    ): AuthRequest {
        $extras = array_merge(['screen' => 'signup'], $extras);

        return $this->createAuthorizationRequest($scopes, $extras, $verifierLength);
    }

    /**
     * @param array<int, string> $scopes
     * @param array<string, string> $extras
     */
    public function authorizationUrl(
        string $state,
        array $scopes = [],
        ?string $codeChallenge = null,
        string $codeChallengeMethod = 'S256',
        array $extras = []
    ): string {
        $scopes = $scopes !== [] ? $scopes : $this->config->defaultScopes;

        $query = array_merge([
            'response_type' => 'code',
            'client_id' => $this->config->clientId,
            'redirect_uri' => $this->config->redirectUri,
            'scope' => implode(' ', $scopes),
            'state' => $state,
        ], $extras);

        if ($codeChallenge) {
            $query['code_challenge'] = $codeChallenge;
            $query['code_challenge_method'] = $codeChallengeMethod;
        }

        return rtrim($this->config->baseUrl, '/').'/oauth/authorize?'.http_build_query($query);
    }

    /**
     * @param array<int, string> $scopes
     * @param array<string, string> $extras
     */
    public function authorizationUrlForSignup(
        string $state,
        array $scopes = [],
        ?string $codeChallenge = null,
        string $codeChallengeMethod = 'S256',
        array $extras = []
    ): string {
        $extras = array_merge(['screen' => 'signup'], $extras);

        return $this->authorizationUrl($state, $scopes, $codeChallenge, $codeChallengeMethod, $extras);
    }

    /**
     * @param array<string, mixed> $query
     */
    public function exchangeCodeFromCallback(array $query, string $expectedState, string $codeVerifier): TokenSet
    {
        if (isset($query['error'])) {
            $message = (string) ($query['error_description'] ?? $query['error'] ?? 'OAuth error');
            throw new UwayConnectException($message, null, $query);
        }

        if (! State::matches($expectedState, isset($query['state']) ? (string) $query['state'] : null)) {
            throw new UwayConnectException('State invalido ou ausente.');
        }

        $code = (string) ($query['code'] ?? '');
        if ($code === '') {
            throw new UwayConnectException('Authorization code ausente.');
        }

        return $this->exchangeCode($code, $codeVerifier);
    }

    /**
     * @param array<string, string> $extras
     */
    public function exchangeCode(string $code, string $codeVerifier, array $extras = []): TokenSet
    {
        $payload = array_merge([
            'grant_type' => 'authorization_code',
            'client_id' => $this->config->clientId,
            'redirect_uri' => $this->config->redirectUri,
            'code' => $code,
            'code_verifier' => $codeVerifier,
        ], $extras);

        if ($this->config->clientSecret !== null && $this->config->clientSecret !== '') {
            $payload['client_secret'] = $this->config->clientSecret;
        }

        $response = $this->requestToken($payload);

        return TokenSet::fromArray($response);
    }

    /**
     * @param array<string, string> $extras
     */
    public function refreshToken(string $refreshToken, array $extras = []): TokenSet
    {
        $payload = array_merge([
            'grant_type' => 'refresh_token',
            'client_id' => $this->config->clientId,
            'refresh_token' => $refreshToken,
        ], $extras);

        if ($this->config->clientSecret !== null && $this->config->clientSecret !== '') {
            $payload['client_secret'] = $this->config->clientSecret;
        }

        $response = $this->requestToken($payload);

        return TokenSet::fromArray($response);
    }

    /**
     * @return array<string, mixed>
     */
    public function userInfo(string $accessToken): array
    {
        try {
            $response = $this->http->request('GET', 'userinfo', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$accessToken,
                ],
            ]);
        } catch (GuzzleException $exception) {
            $this->log('Erro ao consultar userinfo.', ['exception' => $exception->getMessage()]);
            throw new UwayConnectException('Falha ao consultar userinfo.', null, ['error' => $exception->getMessage()]);
        }

        $payload = json_decode((string) $response->getBody(), true);
        if (! is_array($payload)) {
            throw new UwayConnectException('Resposta invalida do userinfo.', $response->getStatusCode());
        }

        return $payload;
    }

    /**
     * @param array<string, string> $payload
     * @return array<string, mixed>
     */
    private function requestToken(array $payload): array
    {
        try {
            $response = $this->http->request('POST', 'token', [
                'headers' => ['Accept' => 'application/json'],
                'form_params' => $payload,
            ]);
        } catch (GuzzleException $exception) {
            $this->log('Erro ao solicitar token.', ['exception' => $exception->getMessage()]);
            throw new UwayConnectException('Falha ao solicitar token.', null, ['error' => $exception->getMessage()]);
        }

        $data = json_decode((string) $response->getBody(), true);

        if (! is_array($data)) {
            throw new UwayConnectException('Resposta invalida do token endpoint.', $response->getStatusCode());
        }

        if (isset($data['error'])) {
            throw new UwayConnectException(
                (string) ($data['error_description'] ?? $data['message'] ?? $data['error']),
                $response->getStatusCode(),
                $data
            );
        }

        return $data;
    }

    private function log(string $message, array $context = []): void
    {
        if (! $this->logger) {
            return;
        }

        $this->logger->warning($message, $context);
    }
}
