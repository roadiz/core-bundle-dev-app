<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Model;

use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;

final class NodeTypeDecoratorPathDto
{
    public function __construct(
        private NodeType $nodeType,
        private ?NodeTypeField $field,
    ) {
    }

    public function getNodeType(): NodeType
    {
        return $this->nodeType;
    }

    public function setNodeType(NodeType $nodeType): NodeTypeDecoratorPathDto
    {
        $this->nodeType = $nodeType;

        return $this;
    }

    public function getField(): ?NodeTypeField
    {
        return $this->field;
    }

    public function setField(?NodeTypeField $field): NodeTypeDecoratorPathDto
    {
        $this->field = $field;

        return $this;
    }
}
