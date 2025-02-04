<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Tests;

use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Contracts\NodeType\SerializableInterface;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Yaml\Yaml;

final class SimpleNodeTypeField implements NodeTypeFieldInterface, SerializableInterface
{
    private ?string $description = null;
    private ?string $label = null;
    private ?string $name = null;
    private ?string $placeholder = null;
    private ?string $defaultValues = null;
    private ?string $groupName = null;
    private array $serializationGroups = [];
    private bool $excludedFromSerialization = false;
    private ?string $serializationExclusionExpression = null;
    private ?int $serializationMaxDepth = null;
    private ?int $minLength = null;
    private ?int $maxLength = null;
    private bool $visible = true;
    private bool $universal = false;
    private bool $searchable = true;
    private bool $virtual = false;
    private bool $indexed = true;
    private bool $expanded = false;
    private string $typeName = 'string';
    private string $nodeTypeName = 'NodeType';
    private string $doctrineType = 'string';

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): SimpleNodeTypeField
    {
        $this->description = $description;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label ?? '';
    }

    public function setLabel(?string $label): SimpleNodeTypeField
    {
        $this->label = $label;

        return $this;
    }

    public function getName(): string
    {
        return $this->name ?? '';
    }

    public function setName(?string $name): SimpleNodeTypeField
    {
        $this->name = $name;

        return $this;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function setPlaceholder(?string $placeholder): SimpleNodeTypeField
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function getDefaultValues(): ?string
    {
        return $this->defaultValues;
    }

    public function setDefaultValues(?string $defaultValues): SimpleNodeTypeField
    {
        $this->defaultValues = $defaultValues;

        return $this;
    }

    public function getDefaultValuesAsArray(): array
    {
        $defaultValues = Yaml::parse($this->defaultValues);
        return is_array($defaultValues) ? $defaultValues : [];
    }

    public function getGroupName(): ?string
    {
        return $this->groupName;
    }

    public function setGroupName(?string $groupName): SimpleNodeTypeField
    {
        $this->groupName = $groupName;

        return $this;
    }

    public function getMinLength(): ?int
    {
        return $this->minLength;
    }

    public function setMinLength(?int $minLength): SimpleNodeTypeField
    {
        $this->minLength = $minLength;

        return $this;
    }

    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }

    public function setMaxLength(?int $maxLength): SimpleNodeTypeField
    {
        $this->maxLength = $maxLength;

        return $this;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): SimpleNodeTypeField
    {
        $this->visible = $visible;

        return $this;
    }

    public function isUniversal(): bool
    {
        return $this->universal;
    }

    public function setUniversal(bool $universal): SimpleNodeTypeField
    {
        $this->universal = $universal;

        return $this;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function setSearchable(bool $searchable): SimpleNodeTypeField
    {
        $this->searchable = $searchable;

        return $this;
    }

    public function isVirtual(): bool
    {
        return $this->virtual;
    }

    public function setVirtual(bool $virtual): SimpleNodeTypeField
    {
        $this->virtual = $virtual;

        return $this;
    }

    public function isIndexed(): bool
    {
        return $this->indexed;
    }

    public function setIndexed(bool $indexed): SimpleNodeTypeField
    {
        $this->indexed = $indexed;

        return $this;
    }

    public function isExpanded(): bool
    {
        return $this->expanded;
    }

    public function setExpanded(bool $expanded): SimpleNodeTypeField
    {
        $this->expanded = $expanded;

        return $this;
    }

    public function getTypeName(): string
    {
        return $this->typeName;
    }

    public function setTypeName(string $typeName): SimpleNodeTypeField
    {
        $this->typeName = $typeName;

        return $this;
    }

    public function getNodeTypeName(): string
    {
        return $this->nodeTypeName;
    }

    public function setNodeTypeName(string $nodeTypeName): SimpleNodeTypeField
    {
        $this->nodeTypeName = $nodeTypeName;

        return $this;
    }

    public function getDoctrineType(): string
    {
        return $this->doctrineType;
    }

    public function setDoctrineType(string $doctrineType): SimpleNodeTypeField
    {
        $this->doctrineType = $doctrineType;

        return $this;
    }

    public function getVarName(): string
    {
        return (new UnicodeString($this->getName()))->camel()->toString();
    }

    public function getGetterName(): string
    {
        return (new UnicodeString('get '.$this->getName()))->camel()->toString();
    }

    public function getSetterName(): string
    {
        return (new UnicodeString('set '.$this->getName()))->camel()->toString();
    }

    public function getGroupNameCanonical(): ?string
    {
        return $this->groupName;
    }

    public function getType()
    {
        return $this->typeName;
    }

    public function isString(): bool
    {
        return 'string' === $this->typeName;
    }

    public function isText(): bool
    {
        return 'text' === $this->typeName;
    }

    public function isDate(): bool
    {
        return 'date' === $this->typeName;
    }

    public function isDateTime(): bool
    {
        return 'datetime' === $this->typeName;
    }

    public function isRichText(): bool
    {
        return 'richtext' === $this->typeName;
    }

    public function isMarkdown(): bool
    {
        return 'markdown' === $this->typeName;
    }

    public function isBool(): bool
    {
        return 'bool' === $this->typeName;
    }

    public function isInteger(): bool
    {
        return 'integer' === $this->typeName;
    }

    public function isDecimal(): bool
    {
        return 'decimal' === $this->typeName;
    }

    public function isEmail(): bool
    {
        return 'email' === $this->typeName;
    }

    public function isDocuments(): bool
    {
        return 'documents' === $this->typeName;
    }

    public function isPassword(): bool
    {
        return 'password' === $this->typeName;
    }

    public function isColor(): bool
    {
        return 'color' === $this->typeName;
    }

    public function isGeoTag(): bool
    {
        return 'geotag' === $this->typeName;
    }

    public function isNodes(): bool
    {
        return 'nodes' === $this->typeName;
    }

    public function isUser(): bool
    {
        return 'user' === $this->typeName;
    }

    public function isEnum(): bool
    {
        return 'enum' === $this->typeName;
    }

    public function isChildrenNodes(): bool
    {
        return 'children' === $this->typeName;
    }

    public function isCustomForms(): bool
    {
        return 'custom_forms' === $this->typeName;
    }

    public function isMultiple(): bool
    {
        return 'multiple' === $this->typeName;
    }

    public function isMultiGeoTag(): bool
    {
        return 'multi_geotag' === $this->typeName;
    }

    public function isJson(): bool
    {
        return 'json' === $this->typeName;
    }

    public function isYaml(): bool
    {
        return 'yaml' === $this->typeName;
    }

    public function isCss(): bool
    {
        return 'css' === $this->typeName;
    }

    public function isManyToMany(): bool
    {
        return 'many_to_many' === $this->typeName;
    }

    public function isManyToOne(): bool
    {
        return 'many_to_one' === $this->typeName;
    }

    public function isCountry(): bool
    {
        return 'country' === $this->typeName;
    }

    public function isSingleProvider(): bool
    {
        return 'single_provider' === $this->typeName;
    }

    public function isMultiProvider(): bool
    {
        return 'multi_provider' === $this->typeName;
    }

    public function isCollection(): bool
    {
        return 'collection' === $this->typeName;
    }

    public function getSerializationGroups(): array
    {
        return $this->serializationGroups;
    }

    public function setSerializationGroups(array $serializationGroups): SimpleNodeTypeField
    {
        $this->serializationGroups = $serializationGroups;

        return $this;
    }

    public function isExcludedFromSerialization(): bool
    {
        return $this->excludedFromSerialization;
    }

    public function setExcludedFromSerialization(bool $excludedFromSerialization): SimpleNodeTypeField
    {
        $this->excludedFromSerialization = $excludedFromSerialization;

        return $this;
    }

    public function getSerializationExclusionExpression(): ?string
    {
        return $this->serializationExclusionExpression;
    }

    public function setSerializationExclusionExpression(?string $serializationExclusionExpression): SimpleNodeTypeField
    {
        $this->serializationExclusionExpression = $serializationExclusionExpression;

        return $this;
    }

    public function getSerializationMaxDepth(): ?int
    {
        return $this->serializationMaxDepth;
    }

    public function setSerializationMaxDepth(?int $serializationMaxDepth): SimpleNodeTypeField
    {
        $this->serializationMaxDepth = $serializationMaxDepth;

        return $this;
    }
}
