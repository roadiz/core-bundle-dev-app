<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\ListManager;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;

interface EntityListManagerFactoryInterface
{
    /**
     * @param class-string<PersistableInterface> $entityClass
     */
    public function createEntityListManager(string $entityClass, array $criteria = [], array $ordering = []): EntityListManagerInterface;

    /**
     * @param class-string<PersistableInterface> $entityClass
     */
    public function createAdminEntityListManager(string $entityClass, array $criteria = [], array $ordering = []): EntityListManagerInterface;
}
