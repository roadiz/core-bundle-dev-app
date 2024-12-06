<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Node;

use Doctrine\Persistence\ManagerRegistry;

final readonly class NodeNamePolicyFactory
{
    public function __construct(
        private ManagerRegistry $registry,
        private bool $useTypedNodeNames,
    ) {
    }

    public function create(): NodeNamePolicyInterface
    {
        return new NodeNameChecker(
            $this->registry,
            $this->useTypedNodeNames
        );
    }
}
