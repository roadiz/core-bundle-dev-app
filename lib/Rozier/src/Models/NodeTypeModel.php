<?php

declare(strict_types=1);

namespace Themes\Rozier\Models;

use RZ\Roadiz\CoreBundle\Entity\NodeType;

/**
 * @package Themes\Rozier\Models
 */
final class NodeTypeModel implements ModelInterface
{
    private NodeType $nodeType;

    /**
     * @param NodeType $nodeType
     */
    public function __construct(NodeType $nodeType)
    {
        $this->nodeType = $nodeType;
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
