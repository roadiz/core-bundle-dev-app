<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Explorer\EventSubscriber;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Explorer\Event\ExplorerEntityListEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class NodeExplorerEntityListEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ExplorerEntityListEvent::class => ['onNodeExplorerEntityList', 100],
        ];
    }

    public function onNodeExplorerEntityList(ExplorerEntityListEvent $event): void
    {
        $entity = $event->getEntityName();

        if (Node::class !== $entity) {
            return;
        }

        $criteria = $event->getCriteria();
        $ordering = $event->getOrdering();

        if (
            !isset($criteria['nodeType'])
            || 1 !== count($criteria['nodeType'])
            || !$criteria['nodeType'][0] instanceof NodeType
        ) {
            return;
        }

        $event->setEntityName($criteria['nodeType'][0]->getSourceEntityFullQualifiedClassName());
        unset($criteria['nodeType']);
        $nodeFields = ['position', 'visible', 'locked', 'status', 'nodeName', 'createdAt', 'updatedAt'];

        // Prefix all criteria array keys names with "node." and recompose criteria array
        $event->setCriteria(array_combine(
            array_map(function ($key) use ($nodeFields) {
                if (in_array($key, $nodeFields)) {
                    return 'node.'.$key;
                }

                return $key;
            }, array_keys($criteria)),
            array_values($criteria)
        ));
        $event->setOrdering(array_combine(
            array_map(function ($key) use ($nodeFields) {
                if (in_array($key, $nodeFields)) {
                    return 'node.'.$key;
                }

                return $key;
            }, array_keys($ordering)),
            array_values($ordering)
        ));
    }
}
