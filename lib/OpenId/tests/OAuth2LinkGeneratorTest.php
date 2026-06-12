<?php

declare(strict_types=1);

namespace RZ\Roadiz\OpenId\Tests;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\OpenId\Discovery;
use RZ\Roadiz\OpenId\OAuth2LinkGenerator;
use RZ\Roadiz\Random\TokenGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class OAuth2LinkGeneratorTest extends TestCase
{
    private function buildDiscovery(): Discovery
    {
        $discovery = $this->createMock(Discovery::class);
        $discovery->method('isValid')->willReturn(true);
        $discovery->method('get')->willReturnCallback(function (string $key, mixed $default = null) {
            return match ($key) {
                'response_types_supported' => ['code'],
                'scopes_supported' => ['openid', 'email', 'profile'],
                'authorization_endpoint' => 'https://accounts.example.com/auth',
                default => $default,
            };
        });

        return $discovery;
    }

    private function buildGenerator(string $nonce = 'test-nonce-abc123'): OAuth2LinkGenerator
    {
        $csrfManager = $this->createMock(CsrfTokenManagerInterface::class);
        $csrfManager->method('getToken')->willReturn(
            new CsrfToken(OAuth2LinkGenerator::OAUTH_STATE_TOKEN, 'csrf-state-token')
        );

        $tokenGenerator = $this->createMock(TokenGeneratorInterface::class);
        $tokenGenerator->method('generateToken')->willReturn($nonce);

        return new OAuth2LinkGenerator(
            $this->buildDiscovery(),
            $csrfManager,
            $tokenGenerator,
            null,
            'test-client-id',
            ['openid', 'email'],
            false,
        );
    }

    private function buildRequest(): Request
    {
        $request = Request::create('https://app.example.com/login');
        $request->setSession(new Session(new MockArraySessionStorage()));

        return $request;
    }

    public function testGenerateStoresNonceInSession(): void
    {
        $request = $this->buildRequest();
        $this->buildGenerator('generated-nonce-value')
            ->generate($request, 'https://app.example.com/openid/callback');

        $this->assertSame(
            'generated-nonce-value',
            $request->getSession()->get(OAuth2LinkGenerator::OAUTH_NONCE_SESSION_KEY)
        );
    }

    public function testNonceInUrlMatchesSessionValue(): void
    {
        $request = $this->buildRequest();
        $url = $this->buildGenerator('unique-nonce-xyz')
            ->generate($request, 'https://app.example.com/openid/callback');

        \parse_str((string) \parse_url($url, PHP_URL_QUERY), $params);
        $storedNonce = $request->getSession()->get(OAuth2LinkGenerator::OAUTH_NONCE_SESSION_KEY);

        $this->assertArrayHasKey('nonce', $params);
        $this->assertSame('unique-nonce-xyz', $params['nonce']);
        $this->assertSame($storedNonce, $params['nonce']);
    }

    public function testGenerateOverwritesPreviousNonce(): void
    {
        $request = $this->buildRequest();
        $request->getSession()->set(OAuth2LinkGenerator::OAUTH_NONCE_SESSION_KEY, 'old-nonce');

        $this->buildGenerator('new-nonce-value')
            ->generate($request, 'https://app.example.com/openid/callback');

        $this->assertSame(
            'new-nonce-value',
            $request->getSession()->get(OAuth2LinkGenerator::OAUTH_NONCE_SESSION_KEY)
        );
    }

    public function testGeneratedNonceIsNonEmpty(): void
    {
        $request = $this->buildRequest();
        $url = $this->buildGenerator('some-nonce')
            ->generate($request, 'https://app.example.com/openid/callback');

        \parse_str((string) \parse_url($url, PHP_URL_QUERY), $params);

        $this->assertNotEmpty($params['nonce']);
        $this->assertNotEmpty($request->getSession()->get(OAuth2LinkGenerator::OAUTH_NONCE_SESSION_KEY));
    }
}
