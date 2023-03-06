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
    private ParameterBag $nodeTypesBag;

    /**
     * @param ParameterBag $nodeTypesBag
     */
    public function __construct(ParameterBag $nodeTypesBag)
    {
        $this->nodeTypesBag = $nodeTypesBag;
    }

    /**
     * @return ParameterBag
     */
    public function getNodeTypesBag(): ParameterBag
    {
        return $this->nodeTypesBag;
    }

    public function getHumanBool(bool $bool): string
    {
        return $bool ? 'true' : 'false';
    }

    /**
     * @param NodeTypeInterface $nodeType
     *
     * @return NodeTypeGenerator
     */
    public function createForNodeType(NodeTypeInterface $nodeType): NodeTypeGenerator
    {
        return new NodeTypeGenerator(
            $nodeType,
            $this
        );
    }

    /**
     * @param NodeTypeFieldInterface $field
     *
     * @return AbstractFieldGenerator
     */
    public function createForNodeTypeField(NodeTypeFieldInterface $field): AbstractFieldGenerator
    {
        switch (true) {
            case $field->isDocuments():
                return new DocumentsFieldGenerator($field, $this->nodeTypesBag);
            case $field->isNodes():
                return new NodeReferencesFieldGenerator($field, $this->nodeTypesBag);
            case $field->isChildrenNodes():
                return new ChildrenNodeFieldGenerator($field, $this->nodeTypesBag);
            case $field->isMultiple():
            case $field->isEnum():
                return new EnumFieldGenerator($field, $this->nodeTypesBag);
            default:
                return new ScalarFieldGenerator($field, $this->nodeTypesBag);
        }
    }
}
