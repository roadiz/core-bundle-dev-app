<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Field;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\Property;

class NonVirtualFieldGenerator extends AbstractFieldGenerator
{
    /**
     * Generate PHP annotation block for Doctrine table indexes.
     */
    #[\Override]
    public function addFieldIndex(ClassType $classType): self
    {
        if ($this->field->isIndexed()) {
            $classType->addAttribute(
                \Doctrine\ORM\Mapping\Index::class,
                [
                    'columns' => [
                        $this->field->getName(),
                    ],
                ]
            );
        }

        return $this;
    }

    protected function getDoctrineType(): string
    {
        return $this->field->getDoctrineType();
    }

    /**
     * @return int|null string field length, returns NULL if length is irrelevant
     */
    protected function getFieldLength(): ?int
    {
        /*
         * Only set length for string (VARCHAR) type
         */
        if ('string' !== $this->getDoctrineType()) {
            return null;
        }

        return match (true) {
            $this->field->isColor() => 10,
            $this->field->isCountry() => 5,
            $this->field->isPassword(), $this->field->isGeoTag() => 128,
            $this->field->isEnum() => $this->defaultValuesResolver->getMaxDefaultValuesLengthAmongAllFields($this->field),
            default => 250,
        };
    }

    #[\Override]
    protected function addFieldAttributes(Property $property, PhpNamespace $namespace, bool $exclude = false): self
    {
        parent::addFieldAttributes($property, $namespace, $exclude);

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
            'name' => $this->field->getName(),
            'type' => $this->getDoctrineType(),
            'nullable' => true,
        ];

        $fieldLength = $this->getFieldLength();
        if (null !== $fieldLength && $fieldLength > 0) {
            $ormParams['length'] = $fieldLength;
        }

        if ($this->field->isDecimal()) {
            $ormParams['precision'] = 18;
            $ormParams['scale'] = 3;
        } elseif ($this->field->isBool()) {
            $ormParams['nullable'] = false;
            $ormParams['options'] = [
                'default' => false,
            ];
        }

        $property->addAttribute(\Gedmo\Mapping\Annotation\Versioned::class);
        $property->addAttribute(\Doctrine\ORM\Mapping\Column::class, $ormParams);

        if (!$this->hasFieldAlternativeGetter() && $this->hasSerializationAttributes()) {
            $this->addSerializationAttributes($property);
        }

        return $this;
    }

    #[\Override]
    public function addFieldAnnotation(Property $property): self
    {
        $this->addFieldAutodoc($property);

        return $this;
    }

    #[\Override]
    protected function getFieldTypeDeclaration(): string
    {
        return match (true) {
            $this->field->isBool() => 'bool',
            $this->field->isMultiple() => '?array',
            $this->field->isInteger(),
            $this->field->isDecimal() => 'int|float|null',
            $this->field->isColor(),
            $this->field->isEmail(),
            $this->field->isString(),
            $this->field->isCountry(),
            $this->field->isMarkdown(),
            $this->field->isText(),
            $this->field->isRichText(),
            $this->field->isEnum() => '?string',
            $this->field->isDateTime(),
            $this->field->isDate() => '?\DateTime',
            default => 'mixed',
        };
    }

    #[\Override]
    protected function getFieldDefaultValueDeclaration(): Literal|string|null
    {
        return match (true) {
            $this->field->isBool() => new Literal('false'),
            default => new Literal('null'),
        };
    }

    #[\Override]
    public function addFieldGetter(ClassType $classType, PhpNamespace $namespace): self
    {
        $type = $this->getFieldTypeDeclaration();
        $method = $classType->addMethod($this->field->getGetterName())
            ->setPublic()
            ->setReturnType($type)
            ->addComment('@return '.$this->toPhpDocType($type));

        if ($this->field->isMultiple()) {
            $method->setBody(
                'return null !== $this->'.
                $this->field->getVarName().' ? array_values($this->'.$this->field->getVarName().') : null;'
            );
        } else {
            $method->setBody('return $this->'.$this->field->getVarName().';');
        }

        return $this;
    }

    #[\Override]
    public function addFieldSetter(ClassType $classType): self
    {
        $assignation = '$'.$this->field->getVarName();
        $nullable = true;
        $casting = '';

        switch (true) {
            case $this->field->isBool():
                $casting = '(bool) ';
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

        if ($nullable && !empty($casting)) {
            $assignation = '$this->'.$this->field->getVarName().' = null !== $'.$this->field->getVarName().' ?
            '.$casting.$assignation.' :
            null;';
        } elseif ($this->field->isMultiple()) {
            $assignation = '$this->'.$this->field->getVarName().' = (null !== '.$assignation.') ? array_values('.$assignation.') : null;';
        } else {
            $assignation = '$this->'.$this->field->getVarName().' = '.$assignation.';';
        }

        $method = $classType->addMethod($this->field->getSetterName())->setPublic();
        $method->setReturnType('static')->addComment('@return $this');
        $method->addParameter($this->field->getVarName())->setType($type);
        $method
            ->addBody($assignation)
            ->addBody('return $this;')
        ;

        return $this;
    }
}
