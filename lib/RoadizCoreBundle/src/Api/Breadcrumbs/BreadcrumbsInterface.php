<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Breadcrumbs;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [])]
interface BreadcrumbsInterface
{
    public function getItems(): array;
}
