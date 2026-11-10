<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\EventSubscriber;

use Scheb\TwoFactorBundle\Security\TwoFactor\Event\TwoFactorAuthenticationEvent;
use Scheb\TwoFactorBundle\Security\TwoFactor\Event\TwoFactorAuthenticationEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;

/**
 * Throttles 2fa_login_check attempts per user, since Scheb 2FA is a separate
 * authentication step that Symfony's firewall-level `login_throttling` does not cover.
 */
final readonly class TwoFactorLoginThrottlingSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RateLimiterFactoryInterface $twoFactorLoginLimiter,
    ) {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            TwoFactorAuthenticationEvents::ATTEMPT => 'onAttempt',
        ];
    }

    public function onAttempt(TwoFactorAuthenticationEvent $event): void
    {
        $limiter = $this->twoFactorLoginLimiter->create($event->getToken()->getUserIdentifier());
        $limit = $limiter->consume();

        if (!$limit->isAccepted()) {
            throw new TooManyLoginAttemptsAuthenticationException((int) ceil(($limit->getRetryAfter()->getTimestamp() - time()) / 60));
        }
    }
}
