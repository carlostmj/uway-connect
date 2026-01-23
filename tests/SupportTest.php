<?php

declare(strict_types=1);

namespace CarlosTMJ\UwayConnect\Tests;

use PHPUnit\Framework\TestCase;
use CarlosTMJ\UwayConnect\Support\Pkce;
use CarlosTMJ\UwayConnect\Support\State;

final class SupportTest extends TestCase
{
    public function testGeneratesVerifierAndChallenge(): void
    {
        $verifier = Pkce::generateVerifier();
        $this->assertGreaterThanOrEqual(43, strlen($verifier));

        $challenge = Pkce::challenge($verifier);
        $this->assertNotSame('', $challenge);
        $this->assertNotSame($verifier, $challenge);
    }

    public function testStateMatches(): void
    {
        $state = State::generate();
        $this->assertTrue(State::matches($state, $state));
        $this->assertFalse(State::matches($state, 'invalid'));
    }
}




