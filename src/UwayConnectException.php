<?php

declare(strict_types=1);

namespace CarlosTMJ\UwayConnect;

use RuntimeException;

/**
 * Excecao disparada pelo SDK quando a integracao com o AUTH falha.
 */
final class UwayConnectException extends RuntimeException
{
    /**
     * @param array<string, mixed>|null $payload
     */
    public function __construct(
        string $message,
        public readonly ?int $statusCode = null,
        public readonly ?array $payload = null
    ) {
        parent::__construct($message);
    }
}
