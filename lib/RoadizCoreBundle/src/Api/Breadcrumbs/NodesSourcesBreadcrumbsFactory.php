<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Breadcrumbs;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;

final class NodesSourcesBreadcrumbsFactory implements BreadcrumbsFactoryInterface
{
    /**
     * @param PersistableInterface|null $entity
     * @return BreadcrumbsInterface|null
     */
    public function create(?PersistableInterface $entity): ?BreadcrumbsInterface
    {
        if (!$entity instanceof NodesSources) {
            return null;
        }

        if (!$entity->isReachable()) {
            return null;
        }

        $parents = [];

        while (null !== $entity = $entity->getParent()) {
            if (
                null !== $entity->getNode() &&
                $entity->getNode()->isVisible()
            ) {
                $parents[] = $entity;
            }
        }

        return new Breadcrumbs(array_reverse($parents));
    }
}
