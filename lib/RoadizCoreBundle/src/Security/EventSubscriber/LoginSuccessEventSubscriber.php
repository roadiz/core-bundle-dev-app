<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class LoginSuccessEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger, private TranslatorInterface $translator)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => ['onLoginSuccess'],
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $this->logger->info($this->translator->trans('User logged in successfully.'), [
            'username' => $event->getRequest()->get('_username'),
            'ip' => $event->getRequest()->getClientIp(),
            'firewall' => $event->getFirewallName(),
        ]);
    }
}
