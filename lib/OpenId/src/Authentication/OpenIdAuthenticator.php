<?php

declare(strict_types=1);

namespace RZ\Roadiz\OpenId\Authentication;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Query;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use RZ\Roadiz\OpenId\Authentication\Provider\JwtRoleStrategy;
use RZ\Roadiz\OpenId\Discovery;
use RZ\Roadiz\OpenId\Exception\DiscoveryNotAvailableException;
use RZ\Roadiz\OpenId\Exception\OpenIdAuthenticationException;
use RZ\Roadiz\OpenId\Exception\OpenIdConfigurationException;
use RZ\Roadiz\OpenId\OpenIdJwtConfigurationFactory;
use RZ\Roadiz\OpenId\User\OpenIdAccount;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

final class OpenIdAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    private Client $client;

    public function __construct(
        private readonly HttpUtils $httpUtils,
        private readonly ?Discovery $discovery,
        private readonly JwtRoleStrategy $roleStrategy,
        private readonly OpenIdJwtConfigurationFactory $jwtConfigurationFactory,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $returnPath,
        private readonly string $defaultRoute,
        private readonly ?string $oauthClientId,
        private readonly ?string $oauthClientSecret,
        private readonly bool $forceSslOnRedirectUri,
        private readonly bool $requiresLocalUsers,
        private readonly string $usernameClaim = 'email',
        private readonly string $targetPathParameter = '_target_path',
        private readonly array $defaultRoles = []
    ) {
        $this->client = new Client([
            // You can set any number of default request options.
            'timeout'  => 2.0,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function supports(Request $request): ?bool
    {
        return null !== $this->discovery &&
            $this->discovery->isValid() &&
            $this->httpUtils->checkRequestPath($request, $this->returnPath) &&
            $request->query->has('state') &&
            $request->query->has('scope') &&
            ($request->query->has('code') || $request->query->has('error'));
    }

    /**
     * @inheritDoc
     */
    public function authenticate(Request $request): Passport
    {
        if (
            null !== $request->query->get('error') &&
            null !== $request->query->get('error_description')
        ) {
            throw new AuthenticationException((string) $request->query->get('error_description'));
        }

        if (null === $this->discovery) {
            throw new DiscoveryNotAvailableException('OpenId discovery service is unavailable, check your configuration.');
        }

        /*
         * Verify CSRF token passed to OAuth2 Service provider,
         * State is an url_encoded string containing the "token" and other
         * optional data
         */
        if (null === $request->query->get('state')) {
            throw new OpenIdAuthenticationException('State is not valid');
        }
        $state = Query::parse((string) $request->query->get('state'));

        /*
         * Fetch _target_path parameter from OAuth2 state
         */
        if (
            isset($state[$this->targetPathParameter])
        ) {
            $request->query->set($this->targetPathParameter, $state[$this->targetPathParameter]);
        }

        try {
            $tokenEndpoint = $this->discovery->get('token_endpoint');
            $redirectUri = $request->getSchemeAndHttpHost() . $request->getBaseUrl() . $request->getPathInfo();

            /*
             * Redirect URI should always use SSL
             */
            if ($this->forceSslOnRedirectUri && str_starts_with($redirectUri, 'http://')) {
                $redirectUri = str_replace('http://', 'https://', $redirectUri);
            }

            if (!\is_string($tokenEndpoint) || empty($tokenEndpoint)) {
                throw new OpenIdConfigurationException('Discovery does not provide a valid token_endpoint.');
            }
            $response = $this->client->post($tokenEndpoint, [
                'form_params' => [
                    'code' => $request->query->get('code'),
                    'client_id' => $this->oauthClientId ?? '',
                    'client_secret' => $this->oauthClientSecret ?? '',
                    'redirect_uri' => $redirectUri,
                    'grant_type' => 'authorization_code'
                ]
            ]);
            /** @var array $jsonResponse */
            $jsonResponse = \json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new OpenIdAuthenticationException(
                'Cannot contact Identity provider to issue authorization_code.' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        if (empty($jsonResponse['id_token'])) {
            throw new OpenIdAuthenticationException('JWT is missing from response.');
        }

        $jwt = $this->jwtConfigurationFactory
            ->create()
            ->parser()
            ->parse((string) $jsonResponse['id_token']);

        if (!($jwt instanceof Plain)) {
            throw new OpenIdAuthenticationException(
                'JWT token must be instance of ' . Plain::class
            );
        }

        if (!$jwt->claims()->has($this->usernameClaim)) {
            throw new OpenIdAuthenticationException(
                'JWT does not contain “' . $this->usernameClaim . '” claim.'
            );
        }

        $username = $jwt->claims()->get($this->usernameClaim);
        if (!\is_string($username) || empty($username)) {
            throw new OpenIdAuthenticationException(
                'JWT “' . $this->usernameClaim . '” claim is not valid.'
            );
        }

        /*
         * Validate JWT token in CustomCredentials
         */
        $customCredentials = new CustomCredentials(
            function (Plain $jwt) {
                $configuration = $this->jwtConfigurationFactory->create();
                $constraints = $configuration->validationConstraints();

                try {
                    $configuration->validator()->assert($jwt, ...$constraints);
                } catch (RequiredConstraintsViolated $e) {
                    throw new OpenIdAuthenticationException($e->getMessage(), 0, $e);
                }
                return true;
            },
            $jwt
        );

        /*
         * If local users are required, we don't need to load user from
         * Identity provider, we can just use local user.
         * But still need to validate JWT token.
         */
        if ($this->requiresLocalUsers) {
            return new Passport(
                new UserBadge($username),
                $customCredentials
            );
        }
        $passport = new Passport(
            new UserBadge($username, function () use ($jwt, $username) {
                /*
                 * Load user from Identity provider, create a virtual user
                 * with roles configured in config/packages/roadiz_rozier.yaml
                 * and need to validate JWT token.
                 */
                return $this->loadUser($jwt->claims()->all(), $username, $jwt);
            }),
            $customCredentials
        );
        $passport->setAttribute('jwt', $jwt);
        $passport->setAttribute('token', !empty($jsonResponse['access_token']) ? $jsonResponse['access_token'] : $jwt->toString());

        return $passport;
    }

    protected function loadUser(array $payload, string $identity, Plain $jwt): UserInterface
    {
        $roles = $this->defaultRoles;
        if ($this->roleStrategy->supports()) {
            $roles = array_merge($roles, $this->roleStrategy->getRoles() ?? []);
        }
        return new OpenIdAccount(
            $identity,
            array_unique($roles),
            $jwt
        );
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate($this->defaultRoute));
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if ($request->hasSession()) {
            $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);
        }
        $url = $this->urlGenerator->generate($this->defaultRoute);

        return new RedirectResponse($url);
    }
}
