<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Event\Node\NodeDuplicatedEvent;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class NodeDuplicationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly HandlerFactoryInterface $handlerFactory
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NodeDuplicatedEvent::class => 'cleanPosition',
        ];
    }

    public function cleanPosition(NodeDuplicatedEvent $event): void
    {
        /** @var NodeHandler $nodeHandler */
        $nodeHandler = $this->handlerFactory->getHandler($event->getNode());
        $nodeHandler->setNode($event->getNode());
        $nodeHandler->cleanChildrenPositions();
        $nodeHandler->cleanPositions();

        $this->managerRegistry->getManager()->flush();
    }
}
