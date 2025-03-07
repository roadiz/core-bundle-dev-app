<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\ListManager;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class EntityListManagerFactory implements EntityListManagerFactoryInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private ManagerRegistry $managerRegistry,
    ) {
    }

    public function createEntityListManager(string $entityClass, array $criteria = [], array $ordering = []): EntityListManagerInterface
    {
        return new EntityListManager(
            $this->requestStack->getCurrentRequest(),
            $this->managerRegistry->getManagerForClass($entityClass),
            $entityClass,
            $criteria,
            $ordering
        );
    }

    public function createAdminEntityListManager(string $entityClass, array $criteria = [], array $ordering = []): EntityListManagerInterface
    {
        return $this->createEntityListManager($entityClass, $criteria, $ordering)
            ->setDisplayingNotPublishedNodes(true);
    }
}
