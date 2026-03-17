<?php

declare(strict_types=1);

namespace CarlosTMJ\UwayConnect;

/**
 * Agrupa os tokens devolvidos pelo AUTH apos code exchange ou refresh.
 */
final class TokenSet
{
    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public readonly string $accessToken,
        public readonly ?string $refreshToken,
        public readonly ?string $idToken,
        public readonly ?int $expiresIn,
        public readonly ?string $scope,
        public readonly ?string $tokenType,
        public readonly array $raw
    ) {
    }

    /**
     * Cria o objeto a partir do payload bruto do token endpoint.
     *
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            (string) ($payload['access_token'] ?? ''),
            isset($payload['refresh_token']) ? (string) $payload['refresh_token'] : null,
            isset($payload['id_token']) ? (string) $payload['id_token'] : null,
            isset($payload['expires_in']) ? (int) $payload['expires_in'] : null,
            isset($payload['scope']) ? (string) $payload['scope'] : null,
            isset($payload['token_type']) ? (string) $payload['token_type'] : null,
            $payload
        );
    }
}
