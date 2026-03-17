<?php

declare(strict_types=1);

namespace CarlosTMJ\UwayConnect;

/**
 * Documento de discovery OpenID retornado pelo UWAY AUTH.
 */
final class DiscoveryDocument
{
    /**
     * @param array<string, mixed> $raw
     * @param array<int, string> $scopesSupported
     * @param array<int, string> $grantTypesSupported
     */
    public function __construct(
        public readonly string $issuer,
        public readonly string $authorizationEndpoint,
        public readonly string $tokenEndpoint,
        public readonly string $userInfoEndpoint,
        public readonly ?string $jwksUri,
        public readonly ?string $accountCenterEndpoint,
        public readonly ?string $accountProfileEndpoint,
        public readonly ?string $accountSecurityEndpoint,
        public readonly ?string $accountSessionsEndpoint,
        public readonly array $scopesSupported,
        public readonly array $grantTypesSupported,
        public readonly array $raw
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            (string) ($payload['issuer'] ?? ''),
            (string) ($payload['authorization_endpoint'] ?? ''),
            (string) ($payload['token_endpoint'] ?? ''),
            (string) ($payload['userinfo_endpoint'] ?? ''),
            isset($payload['jwks_uri']) ? (string) $payload['jwks_uri'] : null,
            isset($payload['uway_account_center_endpoint']) ? (string) $payload['uway_account_center_endpoint'] : null,
            isset($payload['uway_account_profile_endpoint']) ? (string) $payload['uway_account_profile_endpoint'] : null,
            isset($payload['uway_account_security_endpoint']) ? (string) $payload['uway_account_security_endpoint'] : null,
            isset($payload['uway_account_sessions_endpoint']) ? (string) $payload['uway_account_sessions_endpoint'] : null,
            array_values(array_filter(is_array($payload['scopes_supported'] ?? null) ? $payload['scopes_supported'] : [], 'is_string')),
            array_values(array_filter(is_array($payload['grant_types_supported'] ?? null) ? $payload['grant_types_supported'] : [], 'is_string')),
            $payload
        );
    }
}
