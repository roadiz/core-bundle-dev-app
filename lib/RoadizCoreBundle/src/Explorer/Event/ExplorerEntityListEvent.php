<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Explorer\Event;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ExplorerEntityListEvent extends Event
{
    /**
     * @param class-string<PersistableInterface> $entityName
     */
    public function __construct(
        private string $entityName,
        private array $criteria = [],
        private array $ordering = [],
    ) {
    }

    /**
     * @return class-string<PersistableInterface>
     */
    public function getEntityName(): string
    {
        return $this->entityName;
    }

    /**
     * @param class-string<PersistableInterface> $entityName
     */
    public function setEntityName(string $entityName): ExplorerEntityListEvent
    {
        $this->entityName = $entityName;

        return $this;
    }

    public function getCriteria(): array
    {
        return $this->criteria;
    }

    public function setCriteria(array $criteria): ExplorerEntityListEvent
    {
        $this->criteria = $criteria;

        return $this;
    }

    public function getOrdering(): array
    {
        return $this->ordering;
    }

    public function setOrdering(array $ordering): ExplorerEntityListEvent
    {
        $this->ordering = $ordering;

        return $this;
    }
}
