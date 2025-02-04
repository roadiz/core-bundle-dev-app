<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\NodeType;

use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\DependencyInjection\Configuration;
use RZ\Roadiz\EntityGenerator\Field\DefaultValuesResolverInterface;
use Symfony\Component\Yaml\Yaml;

final readonly class DefaultValuesResolver implements DefaultValuesResolverInterface
{
    public function __construct(
        private NodeTypes $nodeTypesBag,
        private string $inheritanceType,
    ) {
    }

    public function getDefaultValuesAmongAllFields(NodeTypeFieldInterface $field): array
    {
        /*
         * With joined inheritance, we can use current field default values because
         * SQL field won't be shared between all node types.
         */
        if (Configuration::INHERITANCE_TYPE_JOINED === $this->inheritanceType) {
            $values = Yaml::parse($field->getDefaultValues());

            return is_array($values) ? $values : [];
        } else {
            /*
             * With single table inheritance, we need to get all default values
             * from all fields of all node types.
             */
            $defaultValues = [];
            $nodeTypeFields = [];
            $nodeTypes = $this->nodeTypesBag->all();
            foreach ($nodeTypes as $nodeType) {
                $nodeTypeFields = [
                    ...$nodeTypeFields,
                    ...$nodeType->getFields()->filter(function (NodeTypeFieldInterface $nodeTypeField) use ($field) {
                        return $nodeTypeField->getName() === $field->getName() && $nodeTypeField->getType() === $field->getType();
                    })->toArray(),
                ];
            }
            foreach ($nodeTypeFields as $nodeTypeField) {
                $values = Yaml::parse($nodeTypeField->getDefaultValues());
                $values = is_array($values) ? array_filter(array_map('trim', $values)) : [];
                $defaultValues = array_merge($defaultValues, $values);
            }

            return $defaultValues;
        }
    }

    public function getMaxDefaultValuesLengthAmongAllFields(NodeTypeFieldInterface $field): int
    {
        // get max length of exploded default values
        $max = 0;
        foreach ($this->getDefaultValuesAmongAllFields($field) as $value) {
            $max = max($max, \mb_strlen($value));
        }

        return $max > 0 ? $max : 250;
    }
}
