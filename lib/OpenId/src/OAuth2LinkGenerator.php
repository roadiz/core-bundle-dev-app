<?php

declare(strict_types=1);

namespace RZ\Roadiz\OpenId;

use RZ\Roadiz\OpenId\Exception\DiscoveryNotAvailableException;
use RZ\Roadiz\Random\TokenGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class OAuth2LinkGenerator
{
    public const OAUTH_STATE_TOKEN = 'openid_state';
    private readonly array $openIdScopes;

    public function __construct(
        protected readonly ?Discovery $discovery,
        protected readonly CsrfTokenManagerInterface $csrfTokenManager,
        protected readonly TokenGeneratorInterface $tokenGenerator,
        protected readonly ?string $openIdHostedDomain,
        protected readonly ?string $oauthClientId,
        ?array $openIdScopes,
        protected readonly bool $forceSslOnRedirectUri,
    ) {
        $this->openIdScopes = array_filter($openIdScopes ?? []);
    }

    public function isSupported(Request $request): bool
    {
        return null !== $this->discovery && $this->discovery->isValid();
    }

    public function generate(
        Request $request,
        string $redirectUri,
        array $state = [],
        string $responseType = 'code',
    ): string {
        if (null === $this->discovery) {
            throw new DiscoveryNotAvailableException('OpenID discovery is not well configured');
        }
        /** @var array $supportedResponseTypes */
        $supportedResponseTypes = $this->discovery->get('response_types_supported', []);
        if (!in_array($responseType, $supportedResponseTypes)) {
            throw new DiscoveryNotAvailableException('OpenID response_type is not supported by your identity provider');
        }

        /*
         * Redirect URI should always use SSL
         */
        if ($this->forceSslOnRedirectUri && str_starts_with($redirectUri, 'http://')) {
            $redirectUri = str_replace('http://', 'https://', $redirectUri);
        }

        /** @var array $supportedScopes */
        $supportedScopes = $this->discovery->get('scopes_supported');

        if (count($this->openIdScopes) > 0 && !empty($this->openIdScopes)) {
            $customScopes = array_intersect(
                $this->openIdScopes,
                $supportedScopes
            );
        } else {
            $customScopes = $supportedScopes;
        }
        $stateToken = $this->csrfTokenManager->getToken(static::OAUTH_STATE_TOKEN);

        return $this->discovery->get('authorization_endpoint').'?'.http_build_query([
            'response_type' => $responseType,
            'hd' => $this->openIdHostedDomain,
            'state' => http_build_query(array_merge($state, [
                'token' => $stateToken->getValue(),
            ])),
            'nonce' => $this->tokenGenerator->generateToken(),
            'login_hint' => $request->get('email', null),
            'scope' => implode(' ', $customScopes),
            'client_id' => $this->oauthClientId,
            'redirect_uri' => $redirectUri,
        ]);
    }
}
