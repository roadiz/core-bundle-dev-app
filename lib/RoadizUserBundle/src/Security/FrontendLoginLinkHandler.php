<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Signature\SignatureHasher;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

final readonly class FrontendLoginLinkHandler implements LoginLinkHandlerInterface
{
    /**
     * @var array|int[]
     */
    private array $options;

    public function __construct(
        private LoginLinkHandlerInterface $decorated,
        private string $frontendLoginCheckRoute,
        private SignatureHasher $signatureHasher,
        private array $frontendLoginLinkRequestRoutes,
        array $options = [],
    ) {
        $this->options = array_merge([
            'lifetime' => 600,
        ], $options);
    }

    public function createLoginLink(
        UserInterface $user,
        ?Request $request = null,
        ?int $lifetime = null,
    ): LoginLinkDetails {
        if (null === $request) {
            throw new \InvalidArgumentException('Request cannot be null.');
        }
        /*
         * If user does not request a login link from `$frontendLoginLinkRequestRoutes`, we fallback to the decorated handler
         */
        if (!\in_array($request->attributes->get('_route'), $this->frontendLoginLinkRequestRoutes, true)) {
            return $this->decorated->createLoginLink($user, $request);
        }

        $expires = time() + ($lifetime ?: $this->options['lifetime']);
        $expiresAt = new \DateTimeImmutable('@'.$expires);

        $parameters = [
            'user' => $user->getUserIdentifier(),
            'expires' => $expires,
            'hash' => $this->signatureHasher->computeSignatureHash($user, $expires),
        ];
        $redirect = $request->getPayload()->get('redirect');
        if (\is_string($redirect)) {
            $parameters['redirect'] = $redirect;
        }

        $url = $this->frontendLoginCheckRoute.'?'.http_build_query($parameters);

        if (false === filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException(sprintf('The URL "%s" is not valid.', $url));
        }

        return new LoginLinkDetails($url, $expiresAt);
    }

    public function consumeLoginLink(Request $request): UserInterface
    {
        return $this->decorated->consumeLoginLink($request);
    }
}
