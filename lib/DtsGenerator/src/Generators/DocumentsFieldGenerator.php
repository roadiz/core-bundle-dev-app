<?php

declare(strict_types=1);

namespace RZ\Roadiz\Typescript\Declaration\Generators;

final class DocumentsFieldGenerator extends AbstractFieldGenerator
{
    protected function getNullableAssertion(): string
    {
        return ''; // always available even if empty
    }

    protected function getType(): string
    {
        return 'Array<RoadizDocument>';
    }
}
