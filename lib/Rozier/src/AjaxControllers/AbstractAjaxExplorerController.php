<?php

declare(strict_types=1);

namespace Themes\Rozier\AjaxControllers;

use RZ\Roadiz\CoreBundle\Explorer\Event\ExplorerEntityListEvent;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerItemFactoryInterface;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class AbstractAjaxExplorerController extends AbstractAjaxController
{
    public function __construct(
        protected readonly ExplorerItemFactoryInterface $explorerItemFactory,
        protected readonly EventDispatcherInterface $eventDispatcher,
        SerializerInterface $serializer,
    ) {
        parent::__construct($serializer);
    }

    public function createEntityListManager(string $entity, array $criteria = [], array $ordering = []): EntityListManagerInterface
    {
        $event = $this->eventDispatcher->dispatch(new ExplorerEntityListEvent($entity, $criteria, $ordering));

        return parent::createEntityListManager($event->getEntityName(), $event->getCriteria(), $event->getOrdering());
    }
}
