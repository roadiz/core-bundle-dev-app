<?php

declare(strict_types=1);

namespace RZ\Roadiz\Typescript\Declaration\Generators;

final class ScalarFieldGenerator extends AbstractFieldGenerator
{
    protected function getType(): string
    {
        switch (true) {
            case $this->field->isString():
            case $this->field->isRichText():
            case $this->field->isText():
            case $this->field->isMarkdown():
            case $this->field->isCss():
            case $this->field->isColor():
            case $this->field->isCountry():
            case $this->field->isDate():
            case $this->field->isDateTime():
            case $this->field->isGeoTag():
            case $this->field->isMultiGeoTag():
                return 'string';
            case $this->field->isBool():
                return 'boolean';
            case $this->field->isDecimal():
            case $this->field->isInteger():
                return 'number';
            case $this->field->isCollection():
            case $this->field->isMultiProvider():
                // Data cannot be known, this depends on user configuration
                return 'Array<unknown>';
            default:
                return 'unknown';
        }
    }
}
