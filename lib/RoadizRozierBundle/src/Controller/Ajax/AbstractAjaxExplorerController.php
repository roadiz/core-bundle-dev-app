<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Explorer\Event\ExplorerEntityListEvent;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerItemFactoryInterface;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerFactoryInterface;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractAjaxExplorerController extends AbstractAjaxController
{
    public function __construct(
        protected readonly ExplorerItemFactoryInterface $explorerItemFactory,
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly EntityListManagerFactoryInterface $entityListManagerFactory,
        ManagerRegistry $managerRegistry,
        SerializerInterface $serializer,
        TranslatorInterface $translator,
    ) {
        parent::__construct($managerRegistry, $serializer, $translator);
    }

    /**
     * @param class-string<PersistableInterface> $entity
     */
    public function createEntityListManager(string $entity, array $criteria = [], array $ordering = []): EntityListManagerInterface
    {
        $event = $this->eventDispatcher->dispatch(new ExplorerEntityListEvent($entity, $criteria, $ordering));

        return $this->entityListManagerFactory->createAdminEntityListManager(
            $event->getEntityName(),
            $event->getCriteria(),
            $event->getOrdering()
        );
    }
}
