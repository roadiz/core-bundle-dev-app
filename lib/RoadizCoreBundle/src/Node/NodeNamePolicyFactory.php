<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Node;

use Doctrine\Persistence\ManagerRegistry;

final class NodeNamePolicyFactory
{
    private ManagerRegistry $registry;
    private bool $useTypedNodeNames;

    /**
     * @param ManagerRegistry $registry
     * @param bool $useTypedNodeNames
     */
    public function __construct(ManagerRegistry $registry, bool $useTypedNodeNames)
    {
        $this->registry = $registry;
        $this->useTypedNodeNames = $useTypedNodeNames;
    }

    public function create(): NodeNamePolicyInterface
    {
        return new NodeNameChecker(
            $this->registry,
            $this->useTypedNodeNames
        );
    }
}
