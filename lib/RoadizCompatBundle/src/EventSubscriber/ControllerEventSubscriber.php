<?php

declare(strict_types=1);

namespace RZ\Roadiz\CompatBundle\EventSubscriber;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RZ\Roadiz\CompatBundle\Controller\AppController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ControllerEventSubscriber implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', -128],
        ];
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (\is_array($controller) && $controller[0] instanceof AppController) {
            $controller[0]->prepareBaseAssignation();
        }
    }
}
