<?php

declare(strict_types=1);

namespace CarlosTMJ\UwayConnect;

final class AuthRequest
{
    /**
     * @param array<int, string> $scopes
     * @param array<string, string> $extras
     */
    public function __construct(
        public readonly string $url,
        public readonly string $state,
        public readonly string $codeVerifier,
        public readonly string $codeChallenge,
        public readonly string $codeChallengeMethod,
        public readonly array $scopes,
        public readonly array $extras
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'state' => $this->state,
            'code_verifier' => $this->codeVerifier,
            'code_challenge' => $this->codeChallenge,
            'code_challenge_method' => $this->codeChallengeMethod,
            'scopes' => $this->scopes,
            'extras' => $this->extras,
        ];
    }
}




