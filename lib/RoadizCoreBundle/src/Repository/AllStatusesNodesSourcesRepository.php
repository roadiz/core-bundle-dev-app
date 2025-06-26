<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

final class AllStatusesNodesSourcesRepository extends NodesSourcesRepository
{
    public function isDisplayingNotPublishedNodes(): bool
    {
        return true;
    }

    public function isDisplayingAllNodesStatuses(): bool
    {
        return true;
    }
}
