<?php

declare(strict_types=1);

namespace CarlosTMJ\UwayConnect;

use InvalidArgumentException;

final class Config
{
    public function __construct(
        public readonly string $baseUrl,
        public readonly string $clientId,
        public readonly ?string $clientSecret,
        public readonly string $redirectUri,
        public readonly array $defaultScopes = ['openid'],
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




