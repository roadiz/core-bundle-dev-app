<?php

declare(strict_types=1);

namespace RZ\Roadiz\Typescript\Declaration;

use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\Typescript\Declaration\Generators\AbstractFieldGenerator;
use RZ\Roadiz\Typescript\Declaration\Generators\ChildrenNodeFieldGenerator;
use RZ\Roadiz\Typescript\Declaration\Generators\DocumentsFieldGenerator;
use RZ\Roadiz\Typescript\Declaration\Generators\EnumFieldGenerator;
use RZ\Roadiz\Typescript\Declaration\Generators\NodeReferencesFieldGenerator;
use RZ\Roadiz\Typescript\Declaration\Generators\NodeTypeGenerator;
use RZ\Roadiz\Typescript\Declaration\Generators\ScalarFieldGenerator;
use Symfony\Component\HttpFoundation\ParameterBag;

final class DeclarationGeneratorFactory
{
    public function __construct(private readonly ParameterBag $nodeTypesBag)
    {
    }

    public function getNodeTypesBag(): ParameterBag
    {
        return $this->nodeTypesBag;
    }

    public function getHumanBool(bool $bool): string
    {
        return $bool ? 'true' : 'false';
    }

    public function createForNodeType(NodeTypeInterface $nodeType): NodeTypeGenerator
    {
        return new NodeTypeGenerator(
            $nodeType,
            $this
        );
    }

    public function createForNodeTypeField(NodeTypeFieldInterface $field): AbstractFieldGenerator
    {
        return match (true) {
            $field->isDocuments() => new DocumentsFieldGenerator($field, $this->nodeTypesBag),
            $field->isNodes() => new NodeReferencesFieldGenerator($field, $this->nodeTypesBag),
            $field->isChildrenNodes() => new ChildrenNodeFieldGenerator($field, $this->nodeTypesBag),
            $field->isMultiple(), $field->isEnum() => new EnumFieldGenerator($field, $this->nodeTypesBag),
            default => new ScalarFieldGenerator($field, $this->nodeTypesBag),
        };
    }
}
