<?php

declare(strict_types=1);

namespace RZ\Roadiz\Random\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use RZ\Roadiz\Random\TokenGenerator;

final class TokenGeneratorTest extends TestCase
{
    private function createTokenGenerator(): TokenGenerator
    {
        return new TokenGenerator(new NullLogger());
    }

    /**
     * generateToken() base64-encodes 32 raw bytes (44 chars with padding) then
     * strips the trailing "=" padding, always leaving 43 chars. A regression that
     * shortens the byte count (or the base64 step) would change this length.
     */
    public function testGenerateTokenLength(): void
    {
        $token = $this->createTokenGenerator()->generateToken();

        self::assertSame(43, \strlen($token));
    }

    public function testGenerateTokenCharset(): void
    {
        $token = $this->createTokenGenerator()->generateToken();

        self::assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $token);
    }

    public function testGenerateTokenIsUnique(): void
    {
        $generator = $this->createTokenGenerator();
        $tokens = [];
        for ($i = 0; $i < 1000; ++$i) {
            $tokens[] = $generator->generateToken();
        }

        self::assertCount(1000, array_unique($tokens));
    }

    /**
     * getRandomNumber() must return raw openssl_random_pseudo_bytes() output (not a
     * time/uniqid-derived string): its length always matches the requested byte count,
     * and RandomGenerator throws instead of falling back if OpenSSL reports non-strong randomness.
     */
    public function testGetRandomNumberReturnsRequestedByteCount(): void
    {
        $randomNumber = $this->createTokenGenerator()->getRandomNumber();

        self::assertSame(32, \strlen($randomNumber));
    }
}
