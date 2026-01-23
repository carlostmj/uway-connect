<?php

declare(strict_types=1);

namespace CarlosTMJ\UwayConnect\Support;

final class Pkce
{
    public static function generateVerifier(int $length = 64): string
    {
        $length = max(43, min(128, $length));
        $bytes = random_bytes($length);

        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }

    public static function challenge(string $verifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    }
}




