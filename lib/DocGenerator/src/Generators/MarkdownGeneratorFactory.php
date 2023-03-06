<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documentation\Generators;

use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Contracts\Translation\TranslatorInterface;

final class MarkdownGeneratorFactory
{
    private ParameterBag $nodeTypesBag;
    private TranslatorInterface $translator;

    /**
     * @param ParameterBag $nodeTypesBag
     * @param TranslatorInterface $translator
     */
    public function __construct(ParameterBag $nodeTypesBag, TranslatorInterface $translator)
    {
        $this->nodeTypesBag = $nodeTypesBag;
        $this->translator = $translator;
    }

    public function getHumanBool(bool $bool): string
    {
        return $bool ? $this->translator->trans('docs.yes') : $this->translator->trans('docs.no');
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
            $this->translator,
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
            case $field->isNodes():
                return new NodeReferencesFieldGenerator($this, $field, $this->nodeTypesBag, $this->translator);
            case $field->isChildrenNodes():
                return new ChildrenNodeFieldGenerator($this, $field, $this->nodeTypesBag, $this->translator);
            case $field->isMultiple():
            case $field->isEnum():
                return new DefaultValuedFieldGenerator($this, $field, $this->nodeTypesBag, $this->translator);
            default:
                return new CommonFieldGenerator($this, $field, $this->nodeTypesBag, $this->translator);
        }
    }
}
