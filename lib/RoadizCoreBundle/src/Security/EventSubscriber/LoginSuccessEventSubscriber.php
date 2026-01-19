<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\EventSubscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\Token\JWTPostAuthenticationToken;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class LoginSuccessEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger, private TranslatorInterface $translator)
    {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => ['onLoginSuccess'],
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $token = $event->getAuthenticatedToken();
        if ($token instanceof JWTPostAuthenticationToken) {
            // Do not log every time a JWT is validated (each request)
            return;
        }

        $this->logger->info($this->translator->trans('User logged in successfully.'), [
            'username' => $token->getUserIdentifier(),
            'ip' => $event->getRequest()->getClientIp(),
            'firewall' => $event->getFirewallName(),
        ]);
    }
}
