<?php

declare(strict_types=1);

namespace CarlosTMJ\UwayConnect;

use RuntimeException;

final class UwayConnectException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $statusCode = null,
        public readonly ?array $payload = null
    ) {
        parent::__construct($message);
    }
}




