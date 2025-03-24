<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Field;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\Property;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Contracts\NodeType\SerializableInterface;
use Symfony\Component\String\UnicodeString;

abstract class AbstractFieldGenerator
{
    public function __construct(
        protected readonly NodeTypeFieldInterface $field,
        protected readonly DefaultValuesResolverInterface $defaultValuesResolver,
        protected array $options = [],
    ) {
    }

    /**
     * Generate PHP code for current doctrine field.
     */
    public function addField(ClassType $classType, PhpNamespace $namespace): void
    {
        $property = $this->getFieldProperty($classType);

        $this
            ->addFieldAnnotation($property)
            ->addFieldAttributes($property, $namespace, $this->isExcludingFieldFromJmsSerialization())
            ->addFieldGetter($classType, $namespace)
            ->addFieldAlternativeGetter($classType)
            ->addFieldSetter($classType)
        ;
    }

    protected function getFieldProperty(ClassType $classType): Property
    {
        return $classType
            ->addProperty($this->field->getVarName())
            ->setPrivate()
            ->setType($this->getFieldTypeDeclaration())
            ->setValue($this->getFieldDefaultValueDeclaration());
    }

    protected function addFieldAutodoc(Property $property): self
    {
        $property->addComment($this->field->getLabel().'.');

        if (!empty($this->field->getDescription())) {
            $property->addComment($this->field->getDescription().'.');
        }
        if (!empty($this->field->getDefaultValues())) {
            $property->addComment('Default values:');
            $property->addComment($this->field->getDefaultValues());
        }
        if (!empty($this->field->getGroupName())) {
            $property->addComment('Group: '.$this->field->getGroupName().'.');
        }

        return $this;
    }

    protected function addFieldAnnotation(Property $property): self
    {
        $this->addFieldAutodoc($property);

        $property->addComment('(Virtual field, this var is a buffer)');

        return $this;
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

    protected function getFieldDefaultValueDeclaration(): Literal|string|null
    {
        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function getNormalizationContext(): ?array
    {
        if (\method_exists($this->field, 'getNormalizationContext')) {
            $normalizationContext = $this->field->getNormalizationContext();
            if (\is_array($normalizationContext) && !empty($normalizationContext['groups'])) {
                return $normalizationContext;
            }
        }

        return null;
    }

    protected function addFieldAttributes(Property $property, PhpNamespace $namespace, bool $exclude = false): self
    {
        if ($exclude) {
            $property->addAttribute('JMS\Serializer\Annotation\Exclude');
        }
        /*
         * Symfony serializer is using getter / setter by default
         */
        if (!$this->excludeFromSerialization()) {
            $property->addAttribute('Symfony\Component\Serializer\Attribute\SerializedName', [
                'serializedName' => $this->field->getVarName(),
            ]);
            $property->addAttribute('Symfony\Component\Serializer\Attribute\Groups', [
                $this->getSerializationGroups(),
            ]);

            $description = $this->field->getLabel();
            if (!empty($this->field->getDescription())) {
                $description .= ': '.$this->field->getDescription();
            }
            $defaultValues = $this->field->getDefaultValuesAsArray();
            if ($this->field->isEnum() && count($defaultValues) > 0) {
                $enumValues = array_filter(array_map('trim', $defaultValues));
                $openapiContext = array_filter([
                    'type' => 'string',
                    'enum' => $enumValues,
                    'example' => $enumValues[0] ?? null,
                ]);
            }

            $property->addAttribute('ApiPlatform\Metadata\ApiProperty', array_filter([
                'description' => $description,
                'example' => $this->field->getPlaceholder(),
                'schema' => $openapiContext ?? null,
                ...$this->getApiPropertyOptions(),
            ]));

            if ($this->getSerializationMaxDepth() > 0) {
                $property->addAttribute('Symfony\Component\Serializer\Attribute\MaxDepth', [
                    $this->getSerializationMaxDepth(),
                ]);
            }

            /*
             * Enable different serialization context for this field.
             */
            if (null !== $this->getNormalizationContext()) {
                $property->addAttribute('Symfony\Component\Serializer\Attribute\Context', [
                    'normalizationContext' => $this->getNormalizationContext(),
                    'groups' => $this->getSerializationGroups(),
                ]);
            }
        }

        if (
            $this->field->isIndexed()
            && true === $this->options['use_api_platform_filters']
        ) {
            switch (true) {
                case $this->field->isString():
                    $property->addAttribute('ApiPlatform\Metadata\ApiFilter', [
                        0 => new Literal($namespace->simplifyName('\ApiPlatform\Doctrine\Orm\Filter\SearchFilter').'::class'),
                        'strategy' => 'partial',
                    ]);
                    $property->addAttribute('ApiPlatform\Metadata\ApiFilter', [
                        new Literal($namespace->simplifyName('\RZ\Roadiz\CoreBundle\Api\Filter\NotFilter').'::class'),
                    ]);
                    break;
                case $this->field->isMultiple():
                case $this->field->isEnum():
                    $property->addAttribute('ApiPlatform\Metadata\ApiFilter', [
                        0 => new Literal($namespace->simplifyName('\ApiPlatform\Doctrine\Orm\Filter\SearchFilter').'::class'),
                        'strategy' => 'exact',
                    ]);
                    $property->addAttribute('ApiPlatform\Metadata\ApiFilter', [
                        new Literal($namespace->simplifyName('\RZ\Roadiz\CoreBundle\Api\Filter\NotFilter').'::class'),
                    ]);
                    break;
                case $this->field->isBool():
                    $property->addAttribute('ApiPlatform\Metadata\ApiFilter', [
                        new Literal($namespace->simplifyName('\ApiPlatform\Doctrine\Orm\Filter\OrderFilter').'::class'),
                    ]);
                    $property->addAttribute('ApiPlatform\Metadata\ApiFilter', [
                        new Literal($namespace->simplifyName('\ApiPlatform\Doctrine\Orm\Filter\BooleanFilter').'::class'),
                    ]);
                    break;
                case $this->field->isManyToOne():
                case $this->field->isManyToMany():
                    $property->addAttribute('ApiPlatform\Metadata\ApiFilter', [
                        new Literal($namespace->simplifyName('\ApiPlatform\Doctrine\Orm\Filter\ExistsFilter').'::class'),
                    ]);
                    break;
                case $this->field->isInteger():
                case $this->field->isDecimal():
                    $property->addAttribute('ApiPlatform\Metadata\ApiFilter', [
                        new Literal($namespace->simplifyName('\ApiPlatform\Doctrine\Orm\Filter\OrderFilter').'::class'),
                    ]);
                    $property->addAttribute('ApiPlatform\Metadata\ApiFilter', [
                        new Literal($namespace->simplifyName('\ApiPlatform\Doctrine\Orm\Filter\NumericFilter').'::class'),
                    ]);
                    $property->addAttribute('ApiPlatform\Metadata\ApiFilter', [
                        new Literal($namespace->simplifyName('\ApiPlatform\Doctrine\Orm\Filter\RangeFilter').'::class'),
                    ]);
                    break;
                case $this->field->isDate():
                case $this->field->isDateTime():
                    $property->addAttribute('ApiPlatform\Metadata\ApiFilter', [
                        new Literal($namespace->simplifyName('\ApiPlatform\Doctrine\Orm\Filter\OrderFilter').'::class'),
                    ]);
                    $property->addAttribute('ApiPlatform\Metadata\ApiFilter', [
                        new Literal($namespace->simplifyName('\ApiPlatform\Doctrine\Orm\Filter\DateFilter').'::class'),
                    ]);
                    break;
            }
        }

        return $this;
    }

    /**
     * Generate PHP alternative getter method block.
     */
    abstract protected function addFieldGetter(ClassType $classType, PhpNamespace $namespace): self;

    /**
     * Generate PHP alternative getter method block.
     */
    protected function addFieldAlternativeGetter(ClassType $classType): self
    {
        return $this;
    }

    /**
     * Generate PHP setter method block.
     */
    protected function addFieldSetter(ClassType $classType): self
    {
        return $this;
    }

    public function getCloneStatements(): string
    {
        return '';
    }

    /**
     * Generate PHP annotation block for Doctrine table indexes.
     */
    public function addFieldIndex(ClassType $classType): self
    {
        return $this;
    }

    /**
     * Generate PHP property initialization for class constructor.
     */
    public function getFieldConstructorInitialization(): string
    {
        return '';
    }

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
            $this->field instanceof SerializableInterface
            && null !== $this->field->getSerializationExclusionExpression()
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
            'nodes_sources_'.($this->field->getGroupNameCanonical() ?: 'default'),
        ];
    }

    protected function getSerializationGroups(): array
    {
        if ($this->field instanceof SerializableInterface && !empty($this->field->getSerializationGroups())) {
            $groups = $this->field->getSerializationGroups();
        } else {
            $groups = $this->getDefaultSerializationGroups();
        }

        return array_map(function (string $group): string {
            return (new UnicodeString($group))
                    ->replaceMatches('/[^A-Za-z0-9]++/', '_')
                    ->trim('_')->toString();
        }, $groups);
    }

    protected function addSerializationAttributes(Property|Method $property): self
    {
        if ($this->excludeFromSerialization()) {
            $property->addAttribute('JMS\Serializer\Annotation\Exclude');
            $property->addAttribute('Symfony\Component\Serializer\Attribute\Ignore');

            return $this;
        }

        $property->addAttribute('JMS\Serializer\Annotation\Groups', [
            $this->getSerializationGroups(),
        ]);

        if ($this->getSerializationMaxDepth() > 0) {
            $property->addAttribute('JMS\Serializer\Annotation\MaxDepth', [
                $this->getSerializationMaxDepth(),
            ]);
        }

        if (null !== $this->getSerializationExclusionExpression()) {
            $property->addAttribute('JMS\Serializer\Annotation\Exclude', [
                'if' => $this->getSerializationExclusionExpression(),
            ]);
        }

        switch (true) {
            case $this->field->isBool():
                $property->addAttribute('JMS\Serializer\Annotation\Type', [
                    'bool',
                ]);
                break;
            case $this->field->isInteger():
                $property->addAttribute('JMS\Serializer\Annotation\Type', [
                    'int',
                ]);
                break;
            case $this->field->isDecimal():
                $property->addAttribute('JMS\Serializer\Annotation\Type', [
                    'double',
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
                $property->addAttribute('JMS\Serializer\Annotation\Type', [
                    'string',
                ]);
                break;
            case $this->field->isDateTime():
            case $this->field->isDate():
                $property->addAttribute('JMS\Serializer\Annotation\Type', [
                    'DateTime',
                ]);
                break;
        }

        return $this;
    }

    protected function hasFieldAlternativeGetter(): bool
    {
        return false;
    }

    protected function hasSerializationAttributes(): bool
    {
        return true;
    }

    protected function isExcludingFieldFromJmsSerialization(): bool
    {
        return true;
    }

    protected function getApiPropertyOptions(): array
    {
        return [];
    }
}
