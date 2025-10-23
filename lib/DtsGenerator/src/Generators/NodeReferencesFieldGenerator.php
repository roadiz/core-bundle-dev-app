<?php

declare(strict_types=1);

namespace RZ\Roadiz\Typescript\Declaration\Generators;

use RZ\Roadiz\Contracts\NodeType\NodeTypeClassLocatorInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

final class NodeReferencesFieldGenerator extends AbstractFieldGenerator
{
    public function __construct(
        private readonly NodeTypeClassLocatorInterface $nodeTypeClassLocator,
        NodeTypeFieldInterface $field,
        ParameterBag $nodeTypesBag,
    ) {
        parent::__construct($field, $nodeTypesBag);
    }

    #[\Override]
    protected function getNullableAssertion(): string
    {
        return ''; // always available even if empty
    }

    #[\Override]
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

        return implode(' | ', array_map(fn (NodeTypeInterface $nodeType) => $this->nodeTypeClassLocator->getSourceEntityClassName($nodeType), $nodeTypes));
    }

    #[\Override]
    protected function getIntroductionLines(): array
    {
        $lines = parent::getIntroductionLines();
        if (!empty($this->field->getDefaultValuesAsArray())) {
            $lines[] = 'Possible values: '.json_encode($this->field->getDefaultValuesAsArray());
        }

        return $lines;
    }
}
