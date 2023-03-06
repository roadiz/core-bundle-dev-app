<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Attribute;

use PHP_CodeSniffer\Tokenizers\PHP;

class AttributeListGenerator
{
    /**
     * @var array<AttributeGenerator>
     */
    public array $attributes;

    /**
     * @param AttributeGenerator[] $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function generate(int $currentIndentation = 0): string
    {
        if (count($this->attributes) === 0) {
            return '';
        }
        if (count($this->attributes) === 1) {
            return sprintf('#[%s]', reset($this->attributes)->generate($currentIndentation));
        }

        return sprintf(
            '%s#[%s%s%s]',
            str_repeat(' ', $currentIndentation),
            PHP_EOL,
            implode(',' . PHP_EOL, array_map(function (AttributeGenerator $attributeGenerator) use ($currentIndentation) {
                return $attributeGenerator->generate($currentIndentation + 4);
            }, $this->attributes)),
            PHP_EOL . str_repeat(' ', $currentIndentation),
        );
    }
}
