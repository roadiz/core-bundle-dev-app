<?php

declare(strict_types=1);

namespace RZ\Roadiz\Typescript\Declaration\Generators;

final class ChildrenNodeFieldGenerator extends AbstractFieldGenerator
{
    #[\Override]
    public function getContents(): string
    {
        return implode(PHP_EOL, [
            $this->getIntroduction(),
        ]);
    }

    #[\Override]
    protected function getIntroductionLines(): array
    {
        $lines = [
            'This node-type uses "blocks" which are available through parent RoadizNodesSources.blocks',
        ];
        if (!empty($this->field->getDefaultValuesAsArray())) {
            $lines[] = 'Possible block node-types: '.json_encode($this->field->getDefaultValuesAsArray());
        }

        return $lines;
    }

    #[\Override]
    protected function getType(): string
    {
        return '';
    }
}
