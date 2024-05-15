<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Field;

use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Contracts\NodeType\SerializableInterface;
use RZ\Roadiz\EntityGenerator\Attribute\AttributeGenerator;
use RZ\Roadiz\EntityGenerator\Attribute\AttributeListGenerator;
use Symfony\Component\String\UnicodeString;

abstract class AbstractFieldGenerator
{
    public const TAB = '    ';
    public const ANNOTATION_PREFIX = AbstractFieldGenerator::TAB . ' *';

    protected NodeTypeFieldInterface $field;
    protected DefaultValuesResolverInterface $defaultValuesResolver;
    protected array $options;

    public function __construct(
        NodeTypeFieldInterface $field,
        DefaultValuesResolverInterface $defaultValuesResolver,
        array $options = []
    ) {
        $this->field = $field;
        $this->defaultValuesResolver = $defaultValuesResolver;
        $this->options = $options;
    }

    /**
     * Generate PHP code for current doctrine field.
     *
     * @return string
     */
    public function getField(): string
    {
        return $this->getFieldAnnotation() .
            (new AttributeListGenerator(
                $this->getFieldAttributes($this->isExcludingFieldFromJmsSerialization())
            )
            )->generate(4) . PHP_EOL .
            $this->getFieldDeclaration() .
            $this->getFieldGetter() .
            $this->getFieldAlternativeGetter() .
            $this->getFieldSetter() . PHP_EOL;
    }

    /**
     * @return array<string>
     */
    protected function getFieldAutodoc(): array
    {
        $docs = [
            $this->field->getLabel() . '.',
        ];
        if (!empty($this->field->getDescription())) {
            $docs[] = $this->field->getDescription() . '.';
        }
        if (!empty($this->field->getDefaultValues())) {
            $docs[] = 'Default values: ' . preg_replace(
                "#(?:\\r\\n|\\n)#",
                PHP_EOL . "     *     ",
                $this->field->getDefaultValues()
            );
        }
        if (!empty($this->field->getGroupName())) {
            $docs[] = 'Group: ' . $this->field->getGroupName() . '.';
        }

        return array_map(function ($line) {
            return (!empty(trim($line))) ? (' ' . $line) : ($line);
        }, $docs);
    }

    /**
     * @return string
     */
    protected function getFieldAnnotation(): string
    {
        $autodoc = '';
        $fieldAutoDoc = $this->getFieldAutodoc();
        if (!empty($fieldAutoDoc)) {
            $autodoc = PHP_EOL .
                static::ANNOTATION_PREFIX .
                implode(PHP_EOL . static::ANNOTATION_PREFIX, $fieldAutoDoc);
        }
        return '
    /**' . $autodoc . '
     *
     * (Virtual field, this var is a buffer)
     */' . PHP_EOL;
    }

    /**
     * Generate PHP property declaration block.
     */
    protected function getFieldDeclaration(): string
    {
        $type = $this->getFieldTypeDeclaration();
        if (!empty($type)) {
            $type .= ' ';
        }
        $defaultValue = $this->getFieldDefaultValueDeclaration();
        if (!empty($defaultValue)) {
            $defaultValue = ' = ' . $defaultValue;
        }
        /*
         * Buffer var to get referenced entities (documents, nodes, custom-forms, doctrine entities)
         */
        return static::TAB . 'private ' . $type . '$' . $this->field->getVarName() . $defaultValue . ';' . PHP_EOL;
    }

    protected function getFieldTypeDeclaration(): string
    {
        return '';
    }

    protected function toPhpDocType(string $typeHint): string
    {
        $unicode = new UnicodeString($typeHint);
        return $unicode->startsWith('?') ?
            $unicode->trimStart('?')->append('|null')->toString() :
            $typeHint;
    }

    protected function getFieldDefaultValueDeclaration(): string
    {
        return '';
    }

    /**
     * @return array<AttributeGenerator>
     */
    protected function getFieldAttributes(bool $exclude = false): array
    {
        $attributes = [];

        if ($exclude) {
            $attributes[] = new AttributeGenerator('Serializer\Exclude');
        }
        /*
         * Symfony serializer is using getter / setter by default
         */
        if (!$this->excludeFromSerialization()) {
            $attributes[] = new AttributeGenerator('SymfonySerializer\SerializedName', [
                'serializedName' => AttributeGenerator::wrapString($this->field->getVarName())
            ]);
            $attributes[] = new AttributeGenerator('SymfonySerializer\Groups', [
                $this->getSerializationGroups()
            ]);

            $description = $this->field->getLabel();
            if (!empty($this->field->getDescription())) {
                $description .= ': ' . $this->field->getDescription();
            }
            if ($this->field->isEnum() && null !== $defaultValues = $this->field->getDefaultValues()) {
                $enumValues = explode(',', $defaultValues);
                $enumValues = array_filter(array_map('trim', $enumValues));
                $openapiContext = [
                    'type' => 'string',
                    'enum' => $enumValues,
                    'example' => $enumValues[0] ?? null,
                ];
            }
            $attributes[] = new AttributeGenerator('\ApiPlatform\Metadata\ApiProperty', [
                'description' => AttributeGenerator::wrapString($description),
                'schema' => $openapiContext ?? null,
                'example' => $this->field->getPlaceholder() ?
                    AttributeGenerator::wrapString($this->field->getPlaceholder()) :
                    null,
            ]);
            if ($this->getSerializationMaxDepth() > 0) {
                $attributes[] = new AttributeGenerator('SymfonySerializer\MaxDepth', [
                    $this->getSerializationMaxDepth()
                ]);
            }
        }

        if (
            $this->field->isIndexed() &&
            $this->options['use_api_platform_filters'] === true
        ) {
            switch (true) {
                case $this->field->isString():
                    $attributes[] = new AttributeGenerator('ApiFilter', [
                        0 => 'OrmFilter\SearchFilter::class',
                        'strategy' => AttributeGenerator::wrapString('partial')
                    ]);
                    $attributes[] = new AttributeGenerator('ApiFilter', [
                        0 => '\RZ\Roadiz\CoreBundle\Api\Filter\NotFilter::class'
                    ]);
                    break;
                case $this->field->isMultiple():
                case $this->field->isEnum():
                    $attributes[] = new AttributeGenerator('ApiFilter', [
                        0 => 'OrmFilter\SearchFilter::class',
                        'strategy' => AttributeGenerator::wrapString('exact')
                    ]);
                    $attributes[] = new AttributeGenerator('ApiFilter', [
                        0 => '\RZ\Roadiz\CoreBundle\Api\Filter\NotFilter::class'
                    ]);
                    break;
                case $this->field->isBool():
                    $attributes[] = new AttributeGenerator('ApiFilter', [
                        'OrmFilter\OrderFilter::class',
                    ]);
                    $attributes[] = new AttributeGenerator('ApiFilter', [
                        'OrmFilter\BooleanFilter::class',
                    ]);
                    break;
                case $this->field->isManyToOne():
                case $this->field->isManyToMany():
                    $attributes[] = new AttributeGenerator('ApiFilter', [
                        'OrmFilter\ExistsFilter::class',
                    ]);
                    break;
                case $this->field->isInteger():
                case $this->field->isDecimal():
                    $attributes[] = new AttributeGenerator('ApiFilter', [
                        'OrmFilter\OrderFilter::class',
                    ]);
                    $attributes[] = new AttributeGenerator('ApiFilter', [
                        'OrmFilter\NumericFilter::class',
                    ]);
                    $attributes[] = new AttributeGenerator('ApiFilter', [
                        'OrmFilter\RangeFilter::class',
                    ]);
                    break;
                case $this->field->isDate():
                case $this->field->isDateTime():
                    $attributes[] = new AttributeGenerator('ApiFilter', [
                        'OrmFilter\OrderFilter::class',
                    ]);
                    $attributes[] = new AttributeGenerator('ApiFilter', [
                        'OrmFilter\DateFilter::class',
                    ]);
                    break;
            }
        }

        return $attributes;
    }

    /**
     * Generate PHP alternative getter method block.
     *
     * @return string
     */
    abstract protected function getFieldGetter(): string;

    /**
     * Generate PHP alternative getter method block.
     *
     * @return string
     */
    protected function getFieldAlternativeGetter(): string
    {
        return '';
    }

    /**
     * Generate PHP setter method block.
     *
     * @return string
     */
    protected function getFieldSetter(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function getCloneStatements(): string
    {
        return '';
    }

    /**
     * Generate PHP annotation block for Doctrine table indexes.
     *
     * @return AttributeGenerator|null
     */
    public function getFieldIndex(): ?AttributeGenerator
    {
        return null;
    }

    /**
     * Generate PHP property initialization for class constructor.
     *
     * @return string
     */
    public function getFieldConstructorInitialization(): string
    {
        return '';
    }

    /**
     * @return bool
     */
    protected function excludeFromSerialization(): bool
    {
        if ($this->field instanceof SerializableInterface) {
            return $this->field->isExcludedFromSerialization();
        }
        return false;
    }

    protected function getSerializationExclusionExpression(): ?string
    {
        if (
            $this->field instanceof SerializableInterface &&
            null !== $this->field->getSerializationExclusionExpression()
        ) {
            return (new UnicodeString($this->field->getSerializationExclusionExpression()))
                ->replace('"', '')
                ->replace('\\', '')
                ->trim()
                ->toString();
        }
        return null;
    }

    protected function getSerializationMaxDepth(): int
    {
        if ($this->field instanceof SerializableInterface && $this->field->getSerializationMaxDepth() > 0) {
            return $this->field->getSerializationMaxDepth();
        }
        return 2;
    }

    protected function getDefaultSerializationGroups(): array
    {
        return [
            'nodes_sources',
            'nodes_sources_' . ($this->field->getGroupNameCanonical() ?: 'default')
        ];
    }

    protected function getSerializationGroups(): string
    {
        if ($this->field instanceof SerializableInterface && !empty($this->field->getSerializationGroups())) {
            $groups = $this->field->getSerializationGroups();
        } else {
            $groups = $this->getDefaultSerializationGroups();
        }
        return '[' . implode(', ', array_map(function (string $group) {
            return '"' . (new UnicodeString($group))
                    ->replaceMatches('/[^A-Za-z0-9]++/', '_')
                    ->trim('_')->toString() . '"';
        }, $groups)) . ']';
    }

    /**
     * @return AttributeGenerator[]
     */
    protected function getSerializationAttributes(): array
    {
        if ($this->excludeFromSerialization()) {
            return [
                new AttributeGenerator('Serializer\Exclude'),
                new AttributeGenerator('SymfonySerializer\Ignore'),
            ];
        }
        $attributes = [];
        $attributes[] = new AttributeGenerator('Serializer\Groups', [
            $this->getSerializationGroups()
        ]);

        if ($this->getSerializationMaxDepth() > 0) {
            $attributes[] = new AttributeGenerator('Serializer\MaxDepth', [
                $this->getSerializationMaxDepth()
            ]);
        }

        if (null !== $this->getSerializationExclusionExpression()) {
            $attributes[] = new AttributeGenerator('Serializer\Exclude', [
                'if' => AttributeGenerator::wrapString($this->getSerializationExclusionExpression())
            ]);
        }

        switch (true) {
            case $this->field->isBool():
                $attributes[] = new AttributeGenerator('Serializer\Type', [
                    AttributeGenerator::wrapString('bool')
                ]);
                break;
            case $this->field->isInteger():
                $attributes[] = new AttributeGenerator('Serializer\Type', [
                    AttributeGenerator::wrapString('int')
                ]);
                break;
            case $this->field->isDecimal():
                $attributes[] = new AttributeGenerator('Serializer\Type', [
                    AttributeGenerator::wrapString('double')
                ]);
                break;
            case $this->field->isColor():
            case $this->field->isEmail():
            case $this->field->isString():
            case $this->field->isCountry():
            case $this->field->isMarkdown():
            case $this->field->isText():
            case $this->field->isRichText():
            case $this->field->isEnum():
                $attributes[] = new AttributeGenerator('Serializer\Type', [
                    AttributeGenerator::wrapString('string')
                ]);
                break;
            case $this->field->isDateTime():
            case $this->field->isDate():
                $attributes[] = new AttributeGenerator('Serializer\Type', [
                    AttributeGenerator::wrapString('DateTime')
                ]);
                break;
        }

        return $attributes;
    }

    protected function isExcludingFieldFromJmsSerialization(): bool
    {
        return true;
    }
}
