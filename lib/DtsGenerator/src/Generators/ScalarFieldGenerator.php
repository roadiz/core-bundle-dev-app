<?php

declare(strict_types=1);

namespace RZ\Roadiz\Typescript\Declaration\Generators;

final class ScalarFieldGenerator extends AbstractFieldGenerator
{
    #[\Override]
    protected function getType(): string
    {
        return match (true) {
            $this->field->isString(),
            $this->field->isRichText(),
            $this->field->isText(),
            $this->field->isMarkdown(),
            $this->field->isCss(),
            $this->field->isColor(),
            $this->field->isCountry(),
            $this->field->isDate(),
            $this->field->isDateTime(),
            $this->field->isGeoTag(),
            $this->field->isMultiGeoTag() => 'string',
            $this->field->isBool() => 'boolean',
            $this->field->isDecimal(), $this->field->isInteger() => 'number',
            $this->field->isCollection(), $this->field->isMultiProvider() => 'Array<unknown>',
            default => 'unknown',
        };
    }
}
