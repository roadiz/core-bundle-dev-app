<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documentation\Generators;

use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;

class ChildrenNodeFieldGenerator extends AbstractFieldGenerator
{
    #[\Override]
    public function getContents(): string
    {
        return implode("\n\n", [
            $this->getIntroduction(),
            '#### '.$this->translator->trans('docs.available_children_blocks'),
            $this->getAvailableChildren(),
        ]);
    }

    /**
     * @return array<NodeTypeInterface>
     */
    protected function getChildrenNodeTypes(): array
    {
        if (null !== $this->field->getDefaultValues()) {
            return array_filter(array_map(function (string $nodeTypeName) {
                $nodeType = $this->nodeTypesBag->get(trim($nodeTypeName));

                return $nodeType instanceof NodeTypeInterface ? $nodeType : null;
            }, $this->field->getDefaultValuesAsArray()));
        }

        return [];
    }

    protected function getAvailableChildren(): string
    {
        return implode("\n", array_map(function (NodeTypeInterface $nodeType) {
            $nodeTypeGenerator = $this->markdownGeneratorFactory->createForNodeType($nodeType);

            return implode("\n", [
                '* **'.trim($nodeTypeGenerator->getMenuEntry()).'**    ',
                $nodeType->getDescription(),
            ]);
        }, $this->getChildrenNodeTypes()))."\n";
    }
}
