<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Node;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Repository\NotPublishedNodeRepository;

final readonly class NodeNamePolicyFactory
{
    public function __construct(
        private ManagerRegistry $registry,
        private NotPublishedNodeRepository $notPublishedNodeRepository,
        private bool $useTypedNodeNames,
    ) {
    }

    public function create(): NodeNamePolicyInterface
    {
        return new NodeNameChecker(
            $this->registry,
            $this->notPublishedNodeRepository,
            $this->useTypedNodeNames,
        );
    }
}
