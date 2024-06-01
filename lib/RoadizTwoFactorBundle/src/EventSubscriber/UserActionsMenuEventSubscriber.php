<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Themes\Rozier\Event\UserActionsMenuEvent;

final class UserActionsMenuEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private UrlGeneratorInterface $urlGenerator, private Security $security)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserActionsMenuEvent::class => 'onUserActionsMenu',
        ];
    }

    public function onUserActionsMenu(UserActionsMenuEvent $event): void
    {
        if ($this->security->isGranted('IS_IMPERSONATOR')) {
            return;
        }
        $event->addAction(
            'two_factor_authentication',
            $this->urlGenerator->generate('2fa_admin_two_factor'),
            'uk-icon-key'
        );
    }
}
