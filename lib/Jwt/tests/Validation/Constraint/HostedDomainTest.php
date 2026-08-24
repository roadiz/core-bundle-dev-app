<?php

declare(strict_types=1);

namespace RZ\Roadiz\JWT\Tests\Validation\Constraint;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\ConstraintViolation;
use PHPUnit\Framework\TestCase;
use RZ\Roadiz\JWT\Validation\Constraint\HostedDomain;

final class HostedDomainTest extends TestCase
{
    private function buildToken(array $claims = []): Plain
    {
        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText(str_repeat('x', 32))
        );
        $builder = $config->builder();
        foreach ($claims as $key => $value) {
            $builder = $builder->withClaim($key, $value);
        }

        return $builder->getToken($config->signer(), $config->signingKey());
    }

    public function testMatchingHostedDomainClaimPasses(): void
    {
        $token = $this->buildToken(['hd' => 'example.com']);

        (new HostedDomain('example.com'))->assert($token);

        $this->addToAssertionCount(1);
    }

    public function testWrongHostedDomainClaimThrowsViolation(): void
    {
        $token = $this->buildToken(['hd' => 'wrong.com']);

        $this->expectException(ConstraintViolation::class);

        (new HostedDomain('example.com'))->assert($token);
    }

    public function testMissingHostedDomainClaimThrowsViolationWhenDomainConfigured(): void
    {
        $token = $this->buildToken();

        $this->expectException(ConstraintViolation::class);

        (new HostedDomain('example.com'))->assert($token);
    }

    /**
     * Documents the intentional fail-open behavior in HostedDomain::assert():
     * an empty configured domain short-circuits the check entirely, even
     * though the token has no 'hd' claim at all. A regression that starts
     * enforcing the check on empty config should make this test fail.
     */
    public function testEmptyConfiguredDomainPassesDueToFailOpenBehavior(): void
    {
        $token = $this->buildToken();

        (new HostedDomain(''))->assert($token);

        $this->addToAssertionCount(1);
    }

    /**
     * Documents the intentional fail-open behavior in HostedDomain::assert():
     * any token that is not a Lcobucci\JWT\Token\Plain instance bypasses the
     * hosted-domain check entirely, regardless of configuration.
     */
    public function testNonPlainTokenPassesDueToFailOpenBehavior(): void
    {
        $token = $this->createMock(Token::class);

        (new HostedDomain('example.com'))->assert($token);

        $this->addToAssertionCount(1);
    }
}
