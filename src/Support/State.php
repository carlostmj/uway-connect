<?php

declare(strict_types=1);

namespace CarlosTMJ\UwayConnect\Support;

/**
 * Helper para geracao e validacao de state no fluxo OAuth.
 */
final class State
{
    /**
     * Gera um state aleatorio em hexadecimal para protecao contra CSRF.
     */
    public static function generate(int $bytes = 24): string
    {
        $bytes = max(16, min(64, $bytes));

        return bin2hex(random_bytes($bytes));
    }

    /**
     * Compara o state esperado com o recebido de forma segura.
     */
    public static function matches(string $expected, ?string $received): bool
    {
        if ($received === null || $received === '') {
            return false;
        }

        return hash_equals($expected, (string) $received);
    }
}
