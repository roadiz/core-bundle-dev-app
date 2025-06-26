<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

final class NotPublishedNodesSourcesRepository extends NodesSourcesRepository
{
    #[\Override]
    public function isDisplayingNotPublishedNodes(): bool
    {
        return true;
    }

    #[\Override]
    public function isDisplayingAllNodesStatuses(): bool
    {
        return false;
    }
}
