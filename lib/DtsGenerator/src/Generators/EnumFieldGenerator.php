<?php

declare(strict_types=1);

namespace RZ\Roadiz\Typescript\Declaration\Generators;

final class EnumFieldGenerator extends AbstractFieldGenerator
{
    #[\Override]
    protected function getType(): string
    {
        switch (true) {
            case $this->field->isEnum():
                $defaultValues = $this->field->getDefaultValuesAsArray();
                if (!empty($defaultValues) && count($defaultValues) > 0) {
                    $defaultValues = array_map(fn (string $value) => '\''.$value.'\'', $defaultValues);

                    return implode(' | ', $defaultValues).' | null';
                }

                return 'string';
            case $this->field->isMultiple():
                return 'Array<string>';
            default:
                return 'any';
        }
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
