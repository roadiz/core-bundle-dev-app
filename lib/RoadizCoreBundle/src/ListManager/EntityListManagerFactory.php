<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\ListManager;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class EntityListManagerFactory implements EntityListManagerFactoryInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private ManagerRegistry $managerRegistry,
    ) {
    }

    #[\Override]
    public function createEntityListManager(string $entityClass, array $criteria = [], array $ordering = []): EntityListManagerInterface
    {
        // Remove leading backslashes on entity class name, getManagerForClass does not like it.
        /** @var class-string<PersistableInterface> $entityClass */
        $entityClass = str_starts_with($entityClass, '\\') ? substr($entityClass, 1) : $entityClass;
        $em = $this->managerRegistry->getManagerForClass($entityClass);

        if (null === $em) {
            throw new \InvalidArgumentException(sprintf('Entity class "%s" does not exist.', $entityClass));
        }

        return new EntityListManager(
            $this->requestStack->getCurrentRequest(),
            $em,
            $entityClass,
            $criteria,
            $ordering
        );
    }

    #[\Override]
    public function createAdminEntityListManager(string $entityClass, array $criteria = [], array $ordering = []): EntityListManagerInterface
    {
        return $this->createEntityListManager($entityClass, $criteria, $ordering)
            ->setDisplayingNotPublishedNodes(true);
    }
}
