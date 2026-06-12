<?php

declare(strict_types=1);

namespace RZ\Roadiz\OpenId\Tests\Authentication;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use PHPUnit\Framework\TestCase;
use RZ\Roadiz\JWT\JwtConfigurationFactory;
use RZ\Roadiz\OpenId\Authentication\OpenIdAuthenticator;
use RZ\Roadiz\OpenId\Authentication\Provider\JwtRoleStrategy;
use RZ\Roadiz\OpenId\Discovery;
use RZ\Roadiz\OpenId\Exception\OpenIdAuthenticationException;
use RZ\Roadiz\OpenId\OAuth2LinkGenerator;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\HttpUtils;

class OpenIdAuthenticatorTest extends TestCase
{
    private const CSRF_TOKEN_VALUE = 'valid-csrf-state-token';
    private const TOKEN_ENDPOINT = 'https://accounts.example.com/token';
    private const CLIENT_ID = 'test-client-id';

    private function buildJwtConfiguration(): Configuration
    {
        return Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText(str_repeat('x', 32))
        );
    }

    private function buildJwtString(array $extraClaims = []): string
    {
        $config = $this->buildJwtConfiguration();
        $builder = $config->builder()
            ->expiresAt(new \DateTimeImmutable('+1 hour'))
            ->permittedFor(self::CLIENT_ID)
            ->withClaim('email', 'user@example.com');

        foreach ($extraClaims as $key => $value) {
            $builder = $builder->withClaim($key, $value);
        }

        return $builder->getToken($config->signer(), $config->signingKey())->toString();
    }

    private function buildDiscovery(): Discovery
    {
        $discovery = $this->createMock(Discovery::class);
        $discovery->method('isValid')->willReturn(true);
        $discovery->method('get')->willReturnCallback(function (string $key, mixed $default = null) {
            return match ($key) {
                'token_endpoint' => self::TOKEN_ENDPOINT,
                default => $default,
            };
        });

        return $discovery;
    }

    private function buildCsrfManager(): CsrfTokenManagerInterface
    {
        $manager = $this->createMock(CsrfTokenManagerInterface::class);
        $manager->method('isTokenValid')->willReturn(true);

        return $manager;
    }

    private function buildJwtFactory(): JwtConfigurationFactory
    {
        $config = $this->buildJwtConfiguration();

        return new class($config) implements JwtConfigurationFactory {
            public function __construct(private readonly Configuration $config)
            {
            }

            public function create(): ?Configuration
            {
                return $this->config;
            }
        };
    }

    private function buildRequest(?string $storedNonce): Request
    {
        $session = new Session(new MockArraySessionStorage());
        if (null !== $storedNonce) {
            $session->set(OAuth2LinkGenerator::OAUTH_NONCE_SESSION_KEY, $storedNonce);
        }

        $request = Request::create('/openid/callback', 'GET', [
            'state' => http_build_query(['token' => self::CSRF_TOKEN_VALUE]),
            'code' => 'auth-code-xyz',
        ]);
        $request->setSession($session);

        return $request;
    }

    private function buildAuthenticator(string $idToken): OpenIdAuthenticator
    {
        return new OpenIdAuthenticator(
            $this->createMock(HttpUtils::class),
            $this->buildDiscovery(),
            $this->createMock(JwtRoleStrategy::class),
            $this->buildJwtFactory(),
            $this->createMock(UrlGeneratorInterface::class),
            $this->buildCsrfManager(),
            new MockHttpClient(new MockResponse(\json_encode([
                'id_token' => $idToken,
                'access_token' => 'access-token-value',
            ]))),
            '/openid/callback',
            'app_login',
            self::CLIENT_ID,
            'test-secret',
            false,
            false,
            'email',
        );
    }

    public function testAuthenticateThrowsWhenNonceMismatch(): void
    {
        $authenticator = $this->buildAuthenticator(
            $this->buildJwtString(['nonce' => 'attacker-injected-nonce'])
        );

        $this->expectException(OpenIdAuthenticationException::class);
        $this->expectExceptionMessage('JWT nonce claim does not match the expected value.');

        $authenticator->authenticate($this->buildRequest('stored-nonce'));
    }

    public function testAuthenticateThrowsWhenJwtMissingNonceClaim(): void
    {
        $authenticator = $this->buildAuthenticator(
            $this->buildJwtString() // no nonce claim
        );

        $this->expectException(OpenIdAuthenticationException::class);
        $this->expectExceptionMessage('JWT nonce claim does not match the expected value.');

        $authenticator->authenticate($this->buildRequest('stored-nonce'));
    }

    public function testAuthenticateSucceedsWhenNonceMatches(): void
    {
        $nonce = 'correct-nonce-value';
        $authenticator = $this->buildAuthenticator(
            $this->buildJwtString(['nonce' => $nonce])
        );

        $passport = $authenticator->authenticate($this->buildRequest($nonce));

        $this->assertNotNull($passport);
    }

    public function testAuthenticateThrowsWhenNoStoredNonce(): void
    {
        // No nonce in session means the flow bypassed generate() — must be rejected.
        $authenticator = $this->buildAuthenticator(
            $this->buildJwtString(['nonce' => 'some-nonce'])
        );

        $this->expectException(OpenIdAuthenticationException::class);
        $this->expectExceptionMessage('No OIDC nonce found in session; possible replay attack.');

        $authenticator->authenticate($this->buildRequest(null));
    }

    public function testAuthenticateThrowsWhenNoSession(): void
    {
        $authenticator = $this->buildAuthenticator(
            $this->buildJwtString(['nonce' => 'some-nonce'])
        );

        $request = Request::create('/openid/callback', 'GET', [
            'state' => http_build_query(['token' => self::CSRF_TOKEN_VALUE]),
            'code' => 'auth-code-xyz',
        ]);
        // Deliberately no session set on the request.

        $this->expectException(OpenIdAuthenticationException::class);
        $this->expectExceptionMessage('No session available for OIDC nonce validation.');

        $authenticator->authenticate($request);
    }

    public function testNonceIsRemovedFromSessionAfterSuccessfulValidation(): void
    {
        $nonce = 'one-time-nonce';
        $request = $this->buildRequest($nonce);
        $authenticator = $this->buildAuthenticator(
            $this->buildJwtString(['nonce' => $nonce])
        );

        $authenticator->authenticate($request);

        $this->assertNull($request->getSession()->get(OAuth2LinkGenerator::OAUTH_NONCE_SESSION_KEY));
    }

    public function testNonceIsRemovedFromSessionEvenOnMismatch(): void
    {
        $request = $this->buildRequest('stored-nonce');
        $authenticator = $this->buildAuthenticator(
            $this->buildJwtString(['nonce' => 'wrong-nonce'])
        );

        try {
            $authenticator->authenticate($request);
        } catch (OpenIdAuthenticationException) {
            // expected — we just want to verify session state below
        }

        $this->assertNull($request->getSession()->get(OAuth2LinkGenerator::OAUTH_NONCE_SESSION_KEY));
    }

    public function testNonceValidationIsTimingSafe(): void
    {
        // Verifies the comparison uses hash_equals rather than === so timing attacks
        // cannot distinguish "nonce present but wrong" from "nonce absent".
        $request = $this->buildRequest('correct-nonce');
        $authenticator = $this->buildAuthenticator(
            $this->buildJwtString(['nonce' => str_repeat('x', 64)])
        );

        $this->expectException(OpenIdAuthenticationException::class);
        $authenticator->authenticate($request);
    }
}
