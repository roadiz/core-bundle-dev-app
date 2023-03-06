<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Field;

use RZ\Roadiz\EntityGenerator\Attribute\AttributeGenerator;

class NonVirtualFieldGenerator extends AbstractFieldGenerator
{
    /**
     * Generate PHP annotation block for Doctrine table indexes.
     *
     * @return AttributeGenerator|null
     */
    public function getFieldIndex(): ?AttributeGenerator
    {
        if ($this->field->isIndexed()) {
            return new AttributeGenerator('ORM\Index', [
                'columns' => '[' . AttributeGenerator::wrapString($this->field->getName()) . ']'
            ]);
        }

        return null;
    }

    /**
     * @return string
     */
    protected function getDoctrineType(): string
    {
        return $this->field->getDoctrineType();
    }

    /**
     * @return int|null String field length, returns NULL if length is irrelevant.
     */
    protected function getFieldLength(): ?int
    {
        /*
         * Only set length for string (VARCHAR) type
         */
        if ($this->getDoctrineType() !== 'string') {
            return null;
        }
        switch (true) {
            case $this->field->isColor():
                return 10;
            case $this->field->isCountry():
                return 5;
            case $this->field->isPassword():
            case $this->field->isGeoTag():
                return 128;
            default:
                return 250;
        }
    }

    protected function isExcludingFieldFromJmsSerialization(): bool
    {
        return false;
    }

    protected function getFieldAttributes(bool $exclude = false): array
    {
        $attributes = parent::getFieldAttributes($exclude);

        /*
         * ?string $name = null,
         * ?string $type = null,
         * ?int $length = null,
         * ?int $precision = null,
         * ?int $scale = null,
         * bool $unique = false,
         * bool $nullable = false,
         * bool $insertable = true,
         * bool $updatable = true,
         * ?string $enumType = null,
         * array $options = [],
         * ?string $columnDefinition = null,
         * ?string $generated = null
         */
        $ormParams = [
            'name' => AttributeGenerator::wrapString($this->field->getName()),
            'type' => AttributeGenerator::wrapString($this->getDoctrineType()),
            'nullable' => 'true',
        ];

        $fieldLength = $this->getFieldLength();
        if (null !== $fieldLength && $fieldLength > 0) {
            $ormParams['length'] = $fieldLength;
        }

        if ($this->field->isDecimal()) {
            $ormParams['precision'] = 18;
            $ormParams['scale'] = 3;
        } elseif ($this->field->isBool()) {
            $ormParams['nullable'] = 'false';
            $ormParams['options'] = '["default" => false]';
        }

        $attributes[] = new AttributeGenerator('Gedmo\Versioned');
        $attributes[] = new AttributeGenerator('ORM\Column', $ormParams);

        if (empty($this->getFieldAlternativeGetter()) && !empty($this->getSerializationAttributes())) {
            return [
                ...$attributes,
                ...$this->getSerializationAttributes()
            ];
        }

        return $attributes;
    }


    /**
     * @inheritDoc
     */
    public function getFieldAnnotation(): string
    {
        $autodoc = '';
        if (!empty($this->getFieldAutodoc())) {
            $autodoc = PHP_EOL .
                static::ANNOTATION_PREFIX .
                implode(PHP_EOL . static::ANNOTATION_PREFIX, $this->getFieldAutodoc());
        }

        return '
    /**' . $autodoc . '
     */' . PHP_EOL;
    }

    protected function getFieldTypeDeclaration(): string
    {
        switch (true) {
            case $this->field->isBool():
                return 'bool';
            case $this->field->isMultiple():
                return '?array';
            case $this->field->isInteger():
            case $this->field->isDecimal():
                return 'int|float|null';
            case $this->field->isColor():
            case $this->field->isEmail():
            case $this->field->isString():
            case $this->field->isCountry():
            case $this->field->isMarkdown():
            case $this->field->isText():
            case $this->field->isRichText():
            case $this->field->isEnum():
                return '?string';
            case $this->field->isDateTime():
            case $this->field->isDate():
                return '?\DateTime';
            default:
                return '';
        }
    }

    protected function getFieldDefaultValueDeclaration(): string
    {
        switch (true) {
            case $this->field->isBool():
                return 'false';
            default:
                return 'null';
        }
    }

    /**
     * @inheritDoc
     */
    public function getFieldGetter(): string
    {
        $type = $this->getFieldTypeDeclaration();
        if (empty($type)) {
            $docType = 'mixed';
            $typeHint = '';
        } else {
            $docType = $this->toPhpDocType($type);
            $typeHint = ': ' . $type;
        }
        $assignation = '$this->' . $this->field->getVarName();

        if ($this->field->isMultiple()) {
            $assignation = sprintf('null !== %s ? array_values(%s) : null', $assignation, $assignation);
        }

        return '
    /**
     * @return ' . $docType . '
     */
    public function ' . $this->field->getGetterName() . '()' . $typeHint . '
    {
        return ' . $assignation . ';
    }' . PHP_EOL;
    }

    /**
     * @inheritDoc
     */
    public function getFieldSetter(): string
    {
        $assignation = '$' . $this->field->getVarName();
        $nullable = true;
        $casting = '';

        switch (true) {
            case $this->field->isBool():
                $casting = '(boolean) ';
                $nullable = false;
                break;
            case $this->field->isInteger():
                $casting = '(int) ';
                break;
            case $this->field->isColor():
            case $this->field->isEmail():
            case $this->field->isString():
            case $this->field->isCountry():
            case $this->field->isMarkdown():
            case $this->field->isText():
            case $this->field->isRichText():
            case $this->field->isEnum():
                $casting = '(string) ';
                break;
        }

        $type = $this->getFieldTypeDeclaration();
        if (empty($type)) {
            $docType = 'mixed';
            $typeHint = '';
        } else {
            $docType = $this->toPhpDocType($type);
            $typeHint = $type . ' ';
        }

        if ($nullable && !empty($casting)) {
            $assignation = '$this->' . $this->field->getVarName() . ' = null !== $' . $this->field->getVarName() . ' ?
            ' . $casting . $assignation . ' :
            null;';
        } else {
            $assignation = '$this->' . $this->field->getVarName() . ' = ' . $assignation . ';';
        }

        return '
    /**
     * @param ' . $docType . ' $' . $this->field->getVarName() . '
     *
     * @return $this
     */
    public function ' . $this->field->getSetterName() . '(' . $typeHint . '$' . $this->field->getVarName() . '): static
    {
        ' . $assignation . '

        return $this;
    }' . PHP_EOL;
    }
}
