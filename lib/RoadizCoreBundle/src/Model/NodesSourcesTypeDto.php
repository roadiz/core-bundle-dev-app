<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

final readonly class NodesSourcesTypeDto
{
    public function __construct(private string $nodeTypeName, private int $nodesSourcesId)
    {
    }

    public function getNodeTypeName(): string
    {
        return $this->nodeTypeName;
    }

    public function getNodesSourcesId(): int
    {
        return $this->nodesSourcesId;
    }
}
