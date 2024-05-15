<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use Gedmo\Loggable\LoggableListener;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class LoggableSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggableListener $loggableListener,
        private readonly Security $security,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
            return;
        }

        if (null === $user = $this->security->getUser()) {
            return;
        }

        if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->loggableListener->setUsername($user);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return array(
            KernelEvents::REQUEST => 'onKernelRequest',
        );
    }
}
