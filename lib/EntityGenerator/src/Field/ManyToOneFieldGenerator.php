<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Field;

use RZ\Roadiz\EntityGenerator\Attribute\AttributeGenerator;

class ManyToOneFieldGenerator extends AbstractConfigurableFieldGenerator
{
    protected function getFieldAttributes(bool $exclude = false): array
    {
        $attributes = parent::getFieldAttributes($exclude);

        /*
         * Many Users have One Address.
         * @ORM\ManyToOne(targetEntity="Address")
         * @ORM\JoinColumn(name="address_id", referencedColumnName="id", onDelete="SET NULL")
         */
        $ormParams = [
            'name' => AttributeGenerator::wrapString($this->field->getName() . '_id'),
            'referencedColumnName' => AttributeGenerator::wrapString('id'),
            'onDelete' => AttributeGenerator::wrapString('SET NULL'),
        ];
        $attributes[] = new AttributeGenerator('ORM\ManyToOne', [
            'targetEntity' => $this->getFullyQualifiedClassName() . '::class'
        ]);
        $attributes[] = new AttributeGenerator('ORM\JoinColumn', $ormParams);

        if ($this->options['use_api_platform_filters'] === true) {
            $attributes[] = new AttributeGenerator('ApiFilter', [
                0 => 'OrmFilter\SearchFilter::class',
                'strategy' => AttributeGenerator::wrapString('exact')
            ]);
        }

        return [
            ...$attributes,
            ...$this->getSerializationAttributes()
        ];
    }

    protected function isExcludingFieldFromJmsSerialization(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getFieldAnnotation(): string
    {
        return '
    /**
     *' . implode(PHP_EOL . static::ANNOTATION_PREFIX, $this->getFieldAutodoc()) . '
     * @var ' . $this->getFullyQualifiedClassName() . '|null
     */' . PHP_EOL;
    }

    protected function getFieldTypeDeclaration(): string
    {
        return '?' . $this->getFullyQualifiedClassName();
    }

    protected function getFieldDefaultValueDeclaration(): string
    {
        return 'null';
    }

    /**
     * @inheritDoc
     */
    public function getFieldGetter(): string
    {
        return '
    /**
     * @return ' . $this->getFullyQualifiedClassName() . '|null
     */
    public function ' . $this->field->getGetterName() . '(): ?' . $this->getFullyQualifiedClassName() . '
    {
        return $this->' . $this->field->getVarName() . ';
    }' . PHP_EOL;
    }

    /**
     * @inheritDoc
     */
    public function getFieldSetter(): string
    {
        return '
    /**
     * @param ' . $this->getFullyQualifiedClassName() . '|null $' . $this->field->getVarName() . '
     * @return $this
     */
    public function ' . $this->field->getSetterName() . '(?' . $this->getFullyQualifiedClassName() . ' $' . $this->field->getVarName() . ' = null): static
    {
        $this->' . $this->field->getVarName() . ' = $' . $this->field->getVarName() . ';

        return $this;
    }' . PHP_EOL;
    }
}
