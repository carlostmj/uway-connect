<?php

declare(strict_types=1);

namespace CarlosTMJ\UwayConnect\Support;

/**
 * Helper utilitario para geracao de valores PKCE.
 */
final class Pkce
{
    /**
     * Gera um code_verifier dentro do intervalo permitido pela especificacao.
     */
    public static function generateVerifier(int $length = 64): string
    {
        $length = max(43, min(128, $length));
        $bytes = random_bytes($length);

        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }

    /**
     * Converte o verifier em code_challenge usando SHA-256 e base64url.
     */
    public static function challenge(string $verifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    }
}
