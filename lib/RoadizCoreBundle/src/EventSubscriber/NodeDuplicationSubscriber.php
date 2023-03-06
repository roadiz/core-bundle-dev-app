<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Event\Node\NodeDuplicatedEvent;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NodeDuplicationSubscriber implements EventSubscriberInterface
{
    protected HandlerFactoryInterface $handlerFactory;
    private ManagerRegistry $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param HandlerFactoryInterface $handlerFactory
     */
    public function __construct(ManagerRegistry $managerRegistry, HandlerFactoryInterface $handlerFactory)
    {
        $this->handlerFactory = $handlerFactory;
        $this->managerRegistry = $managerRegistry;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NodeDuplicatedEvent::class => 'cleanPosition',
            '\RZ\Roadiz\Core\Events\Node\NodeDuplicatedEvent' => 'cleanPosition',
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
