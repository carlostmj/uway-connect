<?php

declare(strict_types=1);

namespace CarlosTMJ\UwayConnect;

use InvalidArgumentException;

/**
 * Configuracao base usada pelo cliente do SDK.
 */
final class Config
{
    /**
     * @param array<int, string> $defaultScopes
     */
    public function __construct(
        public readonly string $baseUrl,
        public readonly string $clientId,
        public readonly ?string $clientSecret,
        public readonly string $redirectUri,
        public readonly array $defaultScopes = ['basic', 'openid'],
        public readonly int $timeoutSeconds = 15,
        public readonly bool $verifySsl = true
    ) {
        if ($this->baseUrl === '' || ! str_starts_with($this->baseUrl, 'http')) {
            throw new InvalidArgumentException('baseUrl invalida.');
        }

        if ($this->clientId === '') {
            throw new InvalidArgumentException('clientId obrigatorio.');
        }

        if ($this->redirectUri === '') {
            throw new InvalidArgumentException('redirectUri obrigatorio.');
        }
    }
}
