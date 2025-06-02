<?php

declare(strict_types=1);

namespace RZ\Roadiz\Typescript\Declaration\Generators;

use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class AbstractFieldGenerator
{
    // Four spaces
    public const INDENTATION_MARK = '    ';

    public function __construct(
        protected readonly NodeTypeFieldInterface $field,
        protected readonly ParameterBag $nodeTypesBag,
    ) {
    }

    protected function getNullableAssertion(): string
    {
        return '?';
    }

    abstract protected function getType(): string;

    private function getDeclaration(): string
    {
        return static::INDENTATION_MARK.
            $this->field->getVarName().
            $this->getNullableAssertion().': '.
            $this->getType();
    }

    public function getContents(): string
    {
        return implode(PHP_EOL, [
            $this->getIntroduction(),
            $this->getDeclaration(),
        ]);
    }

    protected function getIntroductionLines(): array
    {
        $lines = [
            $this->field->getLabel(),
        ];
        if (!empty($this->field->getDescription())) {
            $lines[] = $this->field->getDescription();
        }

        if (!empty($this->field->getGroupName())) {
            $lines[] = 'Group: '.$this->field->getGroupName();
        }

        return $lines;
    }

    public function getIntroduction(): string
    {
        return implode(PHP_EOL, array_map(fn (string $line) => static::INDENTATION_MARK.'// '.$line, $this->getIntroductionLines()));
    }
}
