<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Tests;

use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\EntityGenerator\Field\DefaultValuesResolverInterface;
use Symfony\Component\Yaml\Yaml;

class JoinedTableDefaultValuesResolver implements DefaultValuesResolverInterface
{
    public function getDefaultValuesAmongAllFields(NodeTypeFieldInterface $field): array
    {
        $defaultValues = Yaml::parse($field->getDefaultValues() ?? '') ?? [];
        if (!is_array($defaultValues)) {
            $defaultValues = [];
        }

        return $defaultValues;
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
