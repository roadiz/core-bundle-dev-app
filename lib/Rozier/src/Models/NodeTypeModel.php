<?php

declare(strict_types=1);

namespace Themes\Rozier\Models;

use RZ\Roadiz\CoreBundle\Entity\NodeType;

final class NodeTypeModel implements ModelInterface
{
    public function __construct(private readonly NodeType $nodeType)
    {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->nodeType->getId(),
            'nodeName' => $this->nodeType->getName(),
            'name' => $this->nodeType->getDisplayName(),
            'color' => $this->nodeType->getColor(),
        ];
    }
}
