<?php

declare(strict_types=1);

namespace RZ\Roadiz\OpenId\Tests;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use PHPUnit\Framework\TestCase;
use RZ\Roadiz\JWT\Validation\Constraint\HostedDomain;
use RZ\Roadiz\JWT\Validation\Constraint\UserInfoEndpoint;
use RZ\Roadiz\OpenId\Discovery;
use RZ\Roadiz\OpenId\OpenIdJwtConfigurationFactory;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Pins the constraint set assembled by OpenIdJwtConfigurationFactory, including the
 * SignedWith constraint (M2 fix): the id_token signature is now actually verified,
 * and the verification key is selected by matching the token's "kid" header against
 * the JWKS-derived key map instead of always trusting the first key.
 */
class OpenIdJwtConfigurationFactoryTest extends TestCase
{
    /**
     * @return array{0: string, 1: string} [privateKeyPem, publicKeyPem]
     */
    private function generateRsaKeyPair(): array
    {
        $resource = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        $this->assertNotFalse($resource);
        openssl_pkey_export($resource, $privateKey);
        $publicKey = openssl_pkey_get_details($resource)['key'];

        return [$privateKey, $publicKey];
    }

    private function buildFactory(
        ?Discovery $discovery,
        ?string $hostedDomain,
        ?string $oauthClientId,
        bool $verifyUserInfo,
    ): OpenIdJwtConfigurationFactory {
        return new OpenIdJwtConfigurationFactory(
            $discovery,
            $this->createMock(HttpClientInterface::class),
            $hostedDomain,
            $oauthClientId,
            $verifyUserInfo,
        );
    }

    /**
     * @param array<string, mixed> $values
     */
    private function buildDiscovery(bool $valid, array $values = []): Discovery
    {
        $discovery = $this->createMock(Discovery::class);
        $discovery->method('isValid')->willReturn($valid);
        $discovery->method('get')->willReturnCallback(
            static fn (string $key, mixed $default = null) => $values[$key] ?? $default
        );

        return $discovery;
    }

    /**
     * @return list<class-string>
     */
    private function getValidationConstraintClasses(OpenIdJwtConfigurationFactory $factory, string $pem = 'fake-pem'): array
    {
        $method = new \ReflectionMethod($factory, 'getValidationConstraints');
        $constraints = $method->invoke($factory, $pem);

        return array_map(static fn (object $c) => $c::class, $constraints);
    }

    public function testMinimalConfigOnlyAddsSignedWithAndLooseValidAt(): void
    {
        $factory = $this->buildFactory(null, null, null, false);

        $this->assertSame(
            [SignedWith::class, LooseValidAt::class],
            $this->getValidationConstraintClasses($factory)
        );
    }

    public function testFullConfigAddsAllConstraintsInOrder(): void
    {
        $discovery = $this->buildDiscovery(true, [
            'issuer' => 'https://issuer.example.com',
            'userinfo_endpoint' => 'https://issuer.example.com/userinfo',
        ]);
        $factory = $this->buildFactory($discovery, 'example.com', 'client-id', true);

        $this->assertSame(
            [
                SignedWith::class,
                LooseValidAt::class,
                PermittedFor::class,
                HostedDomain::class,
                IssuedBy::class,
                UserInfoEndpoint::class,
            ],
            $this->getValidationConstraintClasses($factory)
        );
    }

    public function testWithoutHostedDomainSkipsHostedDomainConstraint(): void
    {
        $discovery = $this->buildDiscovery(true, ['issuer' => 'https://issuer.example.com']);
        $factory = $this->buildFactory($discovery, null, 'client-id', false);

        $classes = $this->getValidationConstraintClasses($factory);

        $this->assertNotContains(HostedDomain::class, $classes);
        $this->assertSame(
            [SignedWith::class, LooseValidAt::class, PermittedFor::class, IssuedBy::class],
            $classes
        );
    }

    public function testWithoutOauthClientIdSkipsPermittedForConstraint(): void
    {
        $factory = $this->buildFactory(null, 'example.com', null, false);

        $classes = $this->getValidationConstraintClasses($factory);

        $this->assertNotContains(PermittedFor::class, $classes);
        $this->assertSame([SignedWith::class, LooseValidAt::class, HostedDomain::class], $classes);
    }

    public function testVerifyUserInfoFalseSkipsUserInfoEndpointConstraintEvenWhenAdvertised(): void
    {
        $discovery = $this->buildDiscovery(true, [
            'issuer' => 'https://issuer.example.com',
            'userinfo_endpoint' => 'https://issuer.example.com/userinfo',
        ]);
        $factory = $this->buildFactory($discovery, null, null, false);

        $classes = $this->getValidationConstraintClasses($factory);

        $this->assertNotContains(UserInfoEndpoint::class, $classes);
        $this->assertContains(IssuedBy::class, $classes);
    }

    public function testVerifyUserInfoTrueWithoutUserInfoEndpointSkipsUserInfoEndpointConstraint(): void
    {
        $discovery = $this->buildDiscovery(true, ['issuer' => 'https://issuer.example.com']);
        $factory = $this->buildFactory($discovery, null, null, true);

        $classes = $this->getValidationConstraintClasses($factory);

        $this->assertNotContains(UserInfoEndpoint::class, $classes);
    }

    public function testInvalidDiscoverySkipsIssuedByAndUserInfoEndpointConstraints(): void
    {
        $discovery = $this->buildDiscovery(false, [
            'issuer' => 'https://issuer.example.com',
            'userinfo_endpoint' => 'https://issuer.example.com/userinfo',
        ]);
        $factory = $this->buildFactory($discovery, null, null, true);

        $classes = $this->getValidationConstraintClasses($factory);

        $this->assertNotContains(IssuedBy::class, $classes);
        $this->assertNotContains(UserInfoEndpoint::class, $classes);
        $this->assertSame([SignedWith::class, LooseValidAt::class], $classes);
    }

    public function testCreateReturnsNullWhenDiscoveryIsNull(): void
    {
        $factory = $this->buildFactory(null, null, 'client-id', false);

        $this->assertNull($factory->create());
    }

    public function testCreateReturnsNullWhenSignatureCannotBeVerified(): void
    {
        $discovery = $this->createMock(Discovery::class);
        $discovery->method('canVerifySignature')->willReturn(false);

        $factory = $this->buildFactory($discovery, null, 'client-id', false);

        $this->assertNull($factory->create());
    }

    public function testCreateReturnsNullWhenNoPemsAreAvailable(): void
    {
        $discovery = $this->createMock(Discovery::class);
        $discovery->method('canVerifySignature')->willReturn(true);
        $discovery->method('getPems')->willReturn(null);

        $factory = $this->buildFactory($discovery, null, 'client-id', false);

        $this->assertNull($factory->create());
    }

    public function testCreateReturnsNullWhenRs256IsNotSupported(): void
    {
        $discovery = $this->createMock(Discovery::class);
        $discovery->method('canVerifySignature')->willReturn(true);
        $discovery->method('getPems')->willReturn(['-----BEGIN PUBLIC KEY-----fake-----END PUBLIC KEY-----']);
        $discovery->method('get')->willReturnCallback(
            static fn (string $key, mixed $default = null) => match ($key) {
                'id_token_signing_alg_values_supported' => ['HS256'],
                default => $default,
            }
        );

        $factory = $this->buildFactory($discovery, null, 'client-id', false);

        $this->assertNull($factory->create());
    }

    public function testCreateReturnsNullWhenFirstPemIsEmpty(): void
    {
        $discovery = $this->createMock(Discovery::class);
        $discovery->method('canVerifySignature')->willReturn(true);
        $discovery->method('getPems')->willReturn(['']);
        $discovery->method('get')->willReturnCallback(
            static fn (string $key, mixed $default = null) => match ($key) {
                'id_token_signing_alg_values_supported' => ['RS256'],
                default => $default,
            }
        );

        $factory = $this->buildFactory($discovery, null, 'client-id', false);

        $this->assertNull($factory->create());
    }

    public function testCreateReturnsConfigurationWhenUsableRs256KeyIsAvailable(): void
    {
        $pem = '-----BEGIN PUBLIC KEY-----fake-----END PUBLIC KEY-----';
        $discovery = $this->createMock(Discovery::class);
        $discovery->method('isValid')->willReturn(true);
        $discovery->method('canVerifySignature')->willReturn(true);
        $discovery->method('getPems')->willReturn([$pem]);
        $discovery->method('get')->willReturnCallback(
            static fn (string $key, mixed $default = null) => match ($key) {
                'id_token_signing_alg_values_supported' => ['RS256'],
                'issuer' => 'https://issuer.example.com',
                default => $default,
            }
        );

        $factory = $this->buildFactory($discovery, 'example.com', 'client-id', false);
        $configuration = $factory->create();

        $this->assertInstanceOf(Configuration::class, $configuration);
        $this->assertSame(
            [SignedWith::class, LooseValidAt::class, PermittedFor::class, HostedDomain::class, IssuedBy::class],
            array_map(static fn (object $c) => $c::class, $configuration->validationConstraints())
        );
    }

    public function testCreateSelectsKeyMatchingKidWhenMultipleKeysAreAvailable(): void
    {
        [$privateA, $publicA] = $this->generateRsaKeyPair();
        [, $publicB] = $this->generateRsaKeyPair();

        $discovery = $this->createMock(Discovery::class);
        $discovery->method('canVerifySignature')->willReturn(true);
        $discovery->method('getPems')->willReturn(['key-a' => $publicA, 'key-b' => $publicB]);
        $discovery->method('get')->willReturnCallback(
            static fn (string $key, mixed $default = null) => match ($key) {
                'id_token_signing_alg_values_supported' => ['RS256'],
                default => $default,
            }
        );

        $factory = $this->buildFactory($discovery, null, null, false);
        $signer = new Sha256();
        $signingConfiguration = Configuration::forAsymmetricSigner($signer, InMemory::plainText($privateA), InMemory::plainText($privateA));
        $token = $signingConfiguration->builder()->getToken($signer, InMemory::plainText($privateA));

        $correctKeyConfiguration = $factory->create('key-a');
        $this->assertNotNull($correctKeyConfiguration);
        $correctKeyConfiguration->validator()->assert($token, ...$correctKeyConfiguration->validationConstraints());
        $this->addToAssertionCount(1);
    }

    public function testCreateRejectsTokenSignedWithADifferentKeyThanTheMatchedKid(): void
    {
        [$privateA] = $this->generateRsaKeyPair();
        [, $publicB] = $this->generateRsaKeyPair();

        $discovery = $this->createMock(Discovery::class);
        $discovery->method('canVerifySignature')->willReturn(true);
        $discovery->method('getPems')->willReturn(['key-b' => $publicB]);
        $discovery->method('get')->willReturnCallback(
            static fn (string $key, mixed $default = null) => match ($key) {
                'id_token_signing_alg_values_supported' => ['RS256'],
                default => $default,
            }
        );

        $factory = $this->buildFactory($discovery, null, null, false);
        $signer = new Sha256();
        $signingConfiguration = Configuration::forAsymmetricSigner($signer, InMemory::plainText($privateA), InMemory::plainText($privateA));
        $token = $signingConfiguration->builder()->getToken($signer, InMemory::plainText($privateA));

        $wrongKeyConfiguration = $factory->create('key-b');
        $this->assertNotNull($wrongKeyConfiguration);

        $this->expectException(RequiredConstraintsViolated::class);
        $wrongKeyConfiguration->validator()->assert($token, ...$wrongKeyConfiguration->validationConstraints());
    }

    public function testCreateFallsBackToFirstKeyWhenKidIsUnknown(): void
    {
        $pem = '-----BEGIN PUBLIC KEY-----fake-----END PUBLIC KEY-----';
        $discovery = $this->createMock(Discovery::class);
        $discovery->method('canVerifySignature')->willReturn(true);
        $discovery->method('getPems')->willReturn(['key-a' => $pem]);
        $discovery->method('get')->willReturnCallback(
            static fn (string $key, mixed $default = null) => match ($key) {
                'id_token_signing_alg_values_supported' => ['RS256'],
                default => $default,
            }
        );

        $factory = $this->buildFactory($discovery, null, null, false);

        $this->assertInstanceOf(Configuration::class, $factory->create());
        $this->assertInstanceOf(Configuration::class, $factory->create('unknown-kid'));
    }
}
