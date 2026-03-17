<?php

declare(strict_types=1);

namespace CarlosTMJ\UwayConnect;

final class ExportCapabilityDocument
{
    public const SERVICE = 'uway-user-export';
    public const VERSION = '1.0';
    public const WELL_KNOWN_PATH = '/.well-known/uway-user-export';

    /**
     * @param array<string, mixed> $endpoints
     * @param array<string, mixed> $auth
     * @param array<string, mixed> $userIdentifier
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public readonly string $service,
        public readonly string $version,
        public readonly array $endpoints,
        public readonly array $auth = [],
        public readonly array $userIdentifier = [],
        public readonly array $raw = []
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            (string) ($payload['service'] ?? self::SERVICE),
            (string) ($payload['version'] ?? self::VERSION),
            is_array($payload['endpoints'] ?? null) ? $payload['endpoints'] : [],
            is_array($payload['auth'] ?? null) ? $payload['auth'] : [],
            is_array($payload['user_identifier'] ?? null) ? $payload['user_identifier'] : [],
            $payload
        );
    }

    /**
     * @param array{start: string, status: string, manifest: string, file: string, callback?: string} $endpoints
     * @param array<string, mixed> $auth
     * @param array<string, mixed> $userIdentifier
     */
    public static function make(
        array $endpoints,
        array $auth = ['method' => 'client_credentials', 'scope' => 'internal.user_exports.read'],
        array $userIdentifier = ['primary' => 'uway_user_id', 'fallback' => ['email']]
    ): self {
        return new self(
            self::SERVICE,
            self::VERSION,
            $endpoints,
            $auth,
            $userIdentifier
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'service' => $this->service,
            'version' => $this->version,
            'endpoints' => $this->endpoints,
        ];

        if ($this->auth !== []) {
            $payload['auth'] = $this->auth;
        }

        if ($this->userIdentifier !== []) {
            $payload['user_identifier'] = $this->userIdentifier;
        }

        return $payload;
    }
}
