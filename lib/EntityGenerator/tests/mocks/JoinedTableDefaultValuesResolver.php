<?php

declare(strict_types=1);

namespace tests\mocks;

use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\EntityGenerator\Field\DefaultValuesResolverInterface;

class JoinedTableDefaultValuesResolver implements DefaultValuesResolverInterface
{
    public function getDefaultValuesAmongAllFields(NodeTypeFieldInterface $field): array
    {
        return array_map('trim', explode(',', $field->getDefaultValues()));
    }

    public function getMaxDefaultValuesLengthAmongAllFields(NodeTypeFieldInterface $field): int
    {
        // get max length of exploded default values
        $max = 0;
        foreach ($this->getDefaultValuesAmongAllFields($field) as $value) {
            $max = max($max, strlen($value));
        }

        return $max > 0 ? $max : 250;
    }
}
