<?php

declare(strict_types=1);

namespace RZ\Roadiz\Typescript\Declaration\Generators;

use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;

final class NodeReferencesFieldGenerator extends AbstractFieldGenerator
{
    protected function getNullableAssertion(): string
    {
        return ''; // always available even if empty
    }

    protected function getType(): string
    {
        return 'Array<'.$this->getUnionType().'>';
    }

    /**
     * @return array<NodeTypeInterface>
     */
    private function getLinkedNodeTypes(): array
    {
        $nodeTypeNames = $this->field->getDefaultValuesAsArray();

        if (0 === count($nodeTypeNames)) {
            return $nodeTypeNames;
        }

        return array_values(array_filter(array_map(function (string $name) {
            $nodeType = $this->nodeTypesBag->get(trim($name));

            return $nodeType instanceof NodeTypeInterface ? $nodeType : null;
        }, $nodeTypeNames)));
    }

    private function getUnionType(): string
    {
        $nodeTypes = $this->getLinkedNodeTypes();

        if (empty($nodeTypes)) {
            return 'RoadizNodesSources';
        }

        return implode(' | ', array_map(function (NodeTypeInterface $nodeType) {
            return $nodeType->getSourceEntityClassName();
        }, $nodeTypes));
    }

    protected function getIntroductionLines(): array
    {
        $lines = parent::getIntroductionLines();
        if (!empty($this->field->getDefaultValuesAsArray())) {
            $lines[] = 'Possible values: '.json_encode($this->field->getDefaultValuesAsArray());
        }

        return $lines;
    }
}
