<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Event\FilterUserEvent;
use RZ\Roadiz\CoreBundle\Event\User\UserUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

final readonly class UserLocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        // must be registered after the default Locale listener
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin',
            UserUpdatedEvent::class => 'onUserUpdated',
        ];
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event): void
    {
        if ($this->requestStack->getMainRequest()?->attributes->getBoolean('_stateless')) {
            return;
        }

        $user = $event->getAuthenticationToken()->getUser();

        if (
            $user instanceof User
            && null !== $user->getLocale()
        ) {
            $this->requestStack->getSession()->set('_locale', $user->getLocale());
        }
    }

    public function onUserUpdated(FilterUserEvent $event): void
    {
        if ($this->requestStack->getMainRequest()?->attributes->getBoolean('_stateless')) {
            return;
        }
        $user = $event->getUser();

        if (
            null !== $this->tokenStorage->getToken()
            && $this->tokenStorage->getToken()->getUser() instanceof User
            && $this->tokenStorage->getToken()->getUserIdentifier() === $user->getUserIdentifier()
        ) {
            if (null === $user->getLocale()) {
                $this->requestStack->getSession()->remove('_locale');
            } else {
                $this->requestStack->getSession()->set('_locale', $user->getLocale());
            }
        }
    }
}
