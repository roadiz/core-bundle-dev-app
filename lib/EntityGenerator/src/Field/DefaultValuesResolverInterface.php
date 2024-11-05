<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Field;

use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;

interface DefaultValuesResolverInterface
{
    /**
     * @return array all possible default values for given field name across all node-types
     */
    public function getDefaultValuesAmongAllFields(NodeTypeFieldInterface $field): array;

    /**
     * @return int max length of all possible default values for given field name across all node-types
     */
    public function getMaxDefaultValuesLengthAmongAllFields(NodeTypeFieldInterface $field): int;
}
