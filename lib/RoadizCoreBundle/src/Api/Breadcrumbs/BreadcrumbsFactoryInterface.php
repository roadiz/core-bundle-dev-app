<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Breadcrumbs;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;

interface BreadcrumbsFactoryInterface
{
    public function create(?PersistableInterface $entity): ?BreadcrumbsInterface;
}
