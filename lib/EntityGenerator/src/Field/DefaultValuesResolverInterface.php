<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Field;

use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;

interface DefaultValuesResolverInterface
{
    /**
     * @param NodeTypeFieldInterface $field
     * @return array All possible default values for given field name across all node-types.
     */
    public function getDefaultValuesAmongAllFields(NodeTypeFieldInterface $field): array;

    /**
     * @param NodeTypeFieldInterface $field
     * @return int Max length of all possible default values for given field name across all node-types.
     */
    public function getMaxDefaultValuesLengthAmongAllFields(NodeTypeFieldInterface $field): int;
}
