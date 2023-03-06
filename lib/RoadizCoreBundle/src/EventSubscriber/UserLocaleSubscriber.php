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

final class UserLocaleSubscriber implements EventSubscriberInterface
{
    private RequestStack $requestStack;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        RequestStack $requestStack,
        TokenStorageInterface $tokenStorage
    ) {
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        // must be registered after the default Locale listener
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin',
            UserUpdatedEvent::class => [['onUserUpdated']],
            '\RZ\Roadiz\Core\Events\User\UserUpdatedEvent' => [['onUserUpdated']],
        ];
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if (
            $user instanceof User &&
            null !== $user->getLocale()
        ) {
            $this->requestStack->getSession()->set('_locale', $user->getLocale());
        }
    }

    /**
     * @param FilterUserEvent $event
     */
    public function onUserUpdated(FilterUserEvent $event): void
    {
        $user = $event->getUser();

        if (
            null !== $this->tokenStorage->getToken() &&
            $this->tokenStorage->getToken()->getUser() instanceof User &&
            $this->tokenStorage->getToken()->getUsername() === $user->getUsername()
        ) {
            if (null === $user->getLocale()) {
                $this->requestStack->getSession()->remove('_locale');
            } else {
                $this->requestStack->getSession()->set('_locale', $user->getLocale());
            }
        }
    }
}
