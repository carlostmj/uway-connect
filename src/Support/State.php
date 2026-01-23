<?php

declare(strict_types=1);

namespace CarlosTMJ\UwayConnect\Support;

final class State
{
    public static function generate(int $bytes = 24): string
    {
        $bytes = max(16, min(64, $bytes));

        return bin2hex(random_bytes($bytes));
    }

    public static function matches(string $expected, ?string $received): bool
    {
        if ($received === null || $received === '') {
            return false;
        }

        return hash_equals($expected, (string) $received);
    }
}




