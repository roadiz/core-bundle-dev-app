<?php

declare(strict_types=1);

namespace RZ\Roadiz\JWT\Tests\Validation\Constraint;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\ConstraintViolation;
use PHPUnit\Framework\TestCase;
use RZ\Roadiz\JWT\Validation\Constraint\UserInfoEndpoint;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class UserInfoEndpointTest extends TestCase
{
    private const ENDPOINT = 'https://accounts.example.com/userinfo';

    private function buildToken(): Plain
    {
        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText(str_repeat('x', 32))
        );

        return $config->builder()->getToken($config->signer(), $config->signingKey());
    }

    public function testValidResponsePasses(): void
    {
        $client = new MockHttpClient(new MockResponse('{"sub":"user-1"}', ['http_code' => 200]));

        (new UserInfoEndpoint(self::ENDPOINT, $client))->assert($this->buildToken());

        $this->addToAssertionCount(1);
    }

    public function testUnauthorizedResponseThrowsViolation(): void
    {
        $client = new MockHttpClient(new MockResponse('{"error":"invalid_token"}', ['http_code' => 401]));

        $this->expectException(ConstraintViolation::class);
        $this->expectExceptionMessage('Userinfo cannot be fetch from Identity provider');

        (new UserInfoEndpoint(self::ENDPOINT, $client))->assert($this->buildToken());
    }

    /**
     * A transport-level failure (e.g. connection error) is wrapped into a
     * ConstraintViolation just like an HTTP error status, since the
     * constraint catches the whole Symfony HttpClient ExceptionInterface
     * hierarchy indiscriminately.
     */
    public function testTransportErrorThrowsViolation(): void
    {
        $client = new MockHttpClient(new MockResponse('', ['error' => 'Connection timed out']));

        $this->expectException(ConstraintViolation::class);
        $this->expectExceptionMessage('Userinfo cannot be fetch from Identity provider');

        (new UserInfoEndpoint(self::ENDPOINT, $client))->assert($this->buildToken());
    }
}
