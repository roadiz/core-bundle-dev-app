<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Extension;

use Doctrine\ORM\QueryBuilder;

trait NodesSourcesStatusExtensionTrait
{
    protected function alterQueryBuilderWithStatus(QueryBuilder $queryBuilder, string $alias = 'o'): QueryBuilder
    {
        if ($this->previewResolver->isPreview()) {
            $queryBuilder->andWhere($queryBuilder->expr()->orX(
                $queryBuilder->expr()->isNull($alias.'.deletedAt'),
                $queryBuilder->expr()->gt($alias.'.deletedAt', ':gt_deleted_at')
            ))->setParameter(':gt_deleted_at', new \DateTime());

            return $queryBuilder;
        }

        /*
         * Filter nodes sources by their status.
         */
        $queryBuilder
            ->andWhere($queryBuilder->expr()->lte($alias.'.publishedAt', ':lte_published_at'))
            ->andWhere($queryBuilder->expr()->orX(
                $queryBuilder->expr()->isNull($alias.'.deletedAt'),
                $queryBuilder->expr()->gt($alias.'.deletedAt', ':gt_deleted_at')
            ))
            ->setParameter(':lte_published_at', new \DateTime())
            ->setParameter(':gt_deleted_at', new \DateTime());

        return $queryBuilder;
    }
}
