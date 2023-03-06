<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use Gedmo\Loggable\LoggableListener;
use RZ\Roadiz\CoreBundle\Doctrine\Loggable\UserLoggableListener;
use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class LoggableUsernameSubscriber implements EventSubscriberInterface
{
    private TokenStorageInterface $tokenStorage;
    private LoggableListener $loggableListener;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param LoggableListener $loggableListener
     */
    public function __construct(TokenStorageInterface $tokenStorage, LoggableListener $loggableListener)
    {
        $this->tokenStorage = $tokenStorage;
        $this->loggableListener = $loggableListener;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 33],
        ];
    }

    /**
     * @param RequestEvent $event
     */
    public function onRequest(RequestEvent $event): void
    {
        if ($event->isMainRequest()) {
            $token = $this->tokenStorage->getToken();
            if ($token && $token->getUsername() !== '') {
                if (
                    $this->loggableListener instanceof UserLoggableListener &&
                    $token->getUser() instanceof User
                ) {
                    $this->loggableListener->setUser($token->getUser());
                } else {
                    $this->loggableListener->setUsername($token->getUsername());
                }
            }
        }
    }
}
