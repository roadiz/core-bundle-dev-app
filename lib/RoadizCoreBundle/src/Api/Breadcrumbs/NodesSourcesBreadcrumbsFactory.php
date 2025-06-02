<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Breadcrumbs;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;

final class NodesSourcesBreadcrumbsFactory implements BreadcrumbsFactoryInterface
{
    /*
     * Loop over parents and create a Breadcrumbs object with only visible nodes.
     */
    #[\Override]
    public function create(?PersistableInterface $entity, bool $onlyVisible = true): ?BreadcrumbsInterface
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
                null !== $entity->getNode()
                && (!$onlyVisible || $entity->getNode()->isVisible())
            ) {
                $parents[] = $entity;
            }
        }

        return new Breadcrumbs(array_reverse($parents));
    }
}
