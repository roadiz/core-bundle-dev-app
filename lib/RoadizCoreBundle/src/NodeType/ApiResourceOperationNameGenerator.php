<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\NodeType;

use Symfony\Component\String\UnicodeString;

final class ApiResourceOperationNameGenerator
{
    public function generate(string $resourceClass, string $operation): string
    {
        return sprintf(
            '%s_%s',
            (new UnicodeString($resourceClass))
                ->afterLast('\\')
                ->trimPrefix('NS')
                ->lower()
                ->toString(),
            $operation
        );
    }

    public function generateGetByPath(string $resourceClass): string
    {
        return self::generate($resourceClass, 'get_by_path');
    }
}
