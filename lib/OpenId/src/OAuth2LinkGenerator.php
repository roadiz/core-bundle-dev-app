<?php

declare(strict_types=1);

namespace RZ\Roadiz\OpenId;

use RZ\Roadiz\OpenId\Exception\DiscoveryNotAvailableException;
use RZ\Roadiz\Random\TokenGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class OAuth2LinkGenerator
{
    public const OAUTH_STATE_TOKEN = 'openid_state';

    protected ?Discovery $discovery;
    protected CsrfTokenManagerInterface $csrfTokenManager;
    private ?string $openIdHostedDomain;
    private ?string $oauthClientId;
    private array $openIdScopes;

    public function __construct(
        ?Discovery $discovery,
        CsrfTokenManagerInterface $csrfTokenManager,
        ?string $openIdHostedDomain,
        ?string $oauthClientId,
        ?array $openIdScopes
    ) {
        $this->discovery = $discovery;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->openIdHostedDomain = $openIdHostedDomain;
        $this->oauthClientId = $oauthClientId;
        $this->openIdScopes = array_filter($openIdScopes ?? []);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function isSupported(Request $request): bool
    {
        return null !== $this->discovery && $this->discovery->isValid();
    }

    public function generate(
        Request $request,
        string $redirectUri,
        array $state = [],
        string $responseType = 'code',
        bool $forceSsl = true
    ): string {
        if (null === $this->discovery) {
            throw new DiscoveryNotAvailableException(
                'OpenID discovery is not well configured'
            );
        }
        /** @var array $supportedResponseTypes */
        $supportedResponseTypes = $this->discovery->get('response_types_supported', []);
        if (!in_array($responseType, $supportedResponseTypes)) {
            throw new DiscoveryNotAvailableException(
                'OpenID response_type is not supported by your identity provider'
            );
        }

        /*
         * Redirect URI should always use SSL
         */
        if ($forceSsl && str_starts_with($redirectUri, 'http://')) {
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
        return $this->discovery->get('authorization_endpoint') . '?' . http_build_query([
            'response_type' => 'code',
            'hd' => $this->openIdHostedDomain,
            'state' => http_build_query(array_merge($state, [
                'token' => $stateToken->getValue()
            ])),
            'nonce' => (new TokenGenerator())->generateToken(),
            'login_hint' => $request->get('email', null),
            'scope' => implode(' ', $customScopes),
            'client_id' => $this->oauthClientId,
            'redirect_uri' => $redirectUri,
        ]);
    }
}
