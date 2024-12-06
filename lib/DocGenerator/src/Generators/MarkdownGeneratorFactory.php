<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documentation\Generators;

use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class MarkdownGeneratorFactory
{
    public function __construct(
        private ParameterBag $nodeTypesBag,
        private TranslatorInterface $translator,
    ) {
    }

    public function getHumanBool(bool $bool): string
    {
        return $bool ? $this->translator->trans('docs.yes') : $this->translator->trans('docs.no');
    }

    public function createForNodeType(NodeTypeInterface $nodeType): NodeTypeGenerator
    {
        return new NodeTypeGenerator(
            $nodeType,
            $this->translator,
            $this
        );
    }

    public function createForNodeTypeField(NodeTypeFieldInterface $field): AbstractFieldGenerator
    {
        return match (true) {
            $field->isNodes() => new NodeReferencesFieldGenerator($this, $field, $this->nodeTypesBag, $this->translator),
            $field->isChildrenNodes() => new ChildrenNodeFieldGenerator($this, $field, $this->nodeTypesBag, $this->translator),
            $field->isMultiple(), $field->isEnum() => new DefaultValuedFieldGenerator($this, $field, $this->nodeTypesBag, $this->translator),
            default => new CommonFieldGenerator($this, $field, $this->nodeTypesBag, $this->translator),
        };
    }
}
