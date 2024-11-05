<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Node;

use Doctrine\Persistence\ManagerRegistry;

final class NodeNamePolicyFactory
{
    public function __construct(
        private readonly ManagerRegistry $registry,
        private readonly bool $useTypedNodeNames,
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
