<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\Event;

use Doctrine\ORM\QueryBuilder;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderBuildEvent;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;

abstract class FilterNodesSourcesQueryBuilderCriteriaEvent extends QueryBuilderBuildEvent
{
    /**
     * @param class-string $actualEntityName
     */
    public function __construct(
        QueryBuilder $queryBuilder,
        string $property,
        mixed $value,
        string $actualEntityName,
    ) {
        parent::__construct($queryBuilder, NodesSources::class, $property, $value, $actualEntityName);
    }

    public function supports(): bool
    {
        if (NodesSources::class === $this->actualEntityName) {
            return true;
        }

        $reflectionClass = new \ReflectionClass($this->actualEntityName);
        if ($reflectionClass->isSubclassOf(NodesSources::class)) {
            return true;
        }

        return false;
    }
}
