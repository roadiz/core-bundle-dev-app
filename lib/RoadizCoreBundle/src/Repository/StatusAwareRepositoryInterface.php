<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\ORM\QueryBuilder;

interface StatusAwareRepositoryInterface
{
    public function isDisplayingNotPublishedNodes(): bool;

    public function isDisplayingAllNodesStatuses(): bool;

    public function alterQueryBuilderWithAuthorizationChecker(
        QueryBuilder $queryBuilder,
        string $prefix = EntityRepository::NODE_ALIAS,
    ): QueryBuilder;
}
