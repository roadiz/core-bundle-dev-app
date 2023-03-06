<?php
declare(strict_types=1);

namespace tests\mocks;

use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Contracts\NodeType\SerializableInterface;
use Symfony\Component\String\UnicodeString;

final class NodeTypeField implements NodeTypeFieldInterface, SerializableInterface
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

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return NodeTypeField
     */
    public function setDescription(?string $description): NodeTypeField
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label ?? '';
    }

    /**
     * @param string|null $label
     * @return NodeTypeField
     */
    public function setLabel(?string $label): NodeTypeField
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name ?? '';
    }

    /**
     * @param string|null $name
     * @return NodeTypeField
     */
    public function setName(?string $name): NodeTypeField
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    /**
     * @param string|null $placeholder
     * @return NodeTypeField
     */
    public function setPlaceholder(?string $placeholder): NodeTypeField
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDefaultValues(): ?string
    {
        return $this->defaultValues;
    }

    /**
     * @param string|null $defaultValues
     * @return NodeTypeField
     */
    public function setDefaultValues(?string $defaultValues): NodeTypeField
    {
        $this->defaultValues = $defaultValues;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getGroupName(): ?string
    {
        return $this->groupName;
    }

    /**
     * @param string|null $groupName
     * @return NodeTypeField
     */
    public function setGroupName(?string $groupName): NodeTypeField
    {
        $this->groupName = $groupName;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMinLength(): ?int
    {
        return $this->minLength;
    }

    /**
     * @param int|null $minLength
     * @return NodeTypeField
     */
    public function setMinLength(?int $minLength): NodeTypeField
    {
        $this->minLength = $minLength;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }

    /**
     * @param int|null $maxLength
     * @return NodeTypeField
     */
    public function setMaxLength(?int $maxLength): NodeTypeField
    {
        $this->maxLength = $maxLength;
        return $this;
    }

    /**
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * @param bool $visible
     * @return NodeTypeField
     */
    public function setVisible(bool $visible): NodeTypeField
    {
        $this->visible = $visible;
        return $this;
    }

    /**
     * @return bool
     */
    public function isUniversal(): bool
    {
        return $this->universal;
    }

    /**
     * @param bool $universal
     * @return NodeTypeField
     */
    public function setUniversal(bool $universal): NodeTypeField
    {
        $this->universal = $universal;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    /**
     * @param bool $searchable
     * @return NodeTypeField
     */
    public function setSearchable(bool $searchable): NodeTypeField
    {
        $this->searchable = $searchable;
        return $this;
    }

    /**
     * @return bool
     */
    public function isVirtual(): bool
    {
        return $this->virtual;
    }

    /**
     * @param bool $virtual
     * @return NodeTypeField
     */
    public function setVirtual(bool $virtual): NodeTypeField
    {
        $this->virtual = $virtual;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIndexed(): bool
    {
        return $this->indexed;
    }

    /**
     * @param bool $indexed
     * @return NodeTypeField
     */
    public function setIndexed(bool $indexed): NodeTypeField
    {
        $this->indexed = $indexed;
        return $this;
    }

    /**
     * @return bool
     */
    public function isExpanded(): bool
    {
        return $this->expanded;
    }

    /**
     * @param bool $expanded
     * @return NodeTypeField
     */
    public function setExpanded(bool $expanded): NodeTypeField
    {
        $this->expanded = $expanded;
        return $this;
    }

    /**
     * @return string
     */
    public function getTypeName(): string
    {
        return $this->typeName;
    }

    /**
     * @param string $typeName
     * @return NodeTypeField
     */
    public function setTypeName(string $typeName): NodeTypeField
    {
        $this->typeName = $typeName;
        return $this;
    }

    /**
     * @return string
     */
    public function getNodeTypeName(): string
    {
        return $this->nodeTypeName;
    }

    /**
     * @param string $nodeTypeName
     * @return NodeTypeField
     */
    public function setNodeTypeName(string $nodeTypeName): NodeTypeField
    {
        $this->nodeTypeName = $nodeTypeName;
        return $this;
    }

    /**
     * @return string
     */
    public function getDoctrineType(): string
    {
        return $this->doctrineType;
    }

    /**
     * @param string $doctrineType
     * @return NodeTypeField
     */
    public function setDoctrineType(string $doctrineType): NodeTypeField
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
        return (new UnicodeString('get ' . $this->getName()))->camel()->toString();
    }

    public function getSetterName(): string
    {
        return (new UnicodeString('set ' . $this->getName()))->camel()->toString();
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
        return $this->typeName === 'string';
    }

    public function isText(): bool
    {
        return $this->typeName === 'text';
    }

    public function isDate(): bool
    {
        return $this->typeName === 'date';
    }

    public function isDateTime(): bool
    {
        return $this->typeName === 'datetime';
    }

    public function isRichText(): bool
    {
        return $this->typeName === 'richtext';
    }

    public function isMarkdown(): bool
    {
        return $this->typeName === 'markdown';
    }

    public function isBool(): bool
    {
        return $this->typeName === 'bool';
    }

    public function isInteger(): bool
    {
        return $this->typeName === 'integer';
    }

    public function isDecimal(): bool
    {
        return $this->typeName === 'decimal';
    }

    public function isEmail(): bool
    {
        return $this->typeName === 'email';
    }

    public function isDocuments(): bool
    {
        return $this->typeName === 'documents';
    }

    public function isPassword(): bool
    {
        return $this->typeName === 'password';
    }

    public function isColor(): bool
    {
        return $this->typeName === 'color';
    }

    public function isGeoTag(): bool
    {
        return $this->typeName === 'geotag';
    }

    public function isNodes(): bool
    {
        return $this->typeName === 'nodes';
    }

    public function isUser(): bool
    {
        return $this->typeName === 'user';
    }

    public function isEnum(): bool
    {
        return $this->typeName === 'enum';
    }

    public function isChildrenNodes(): bool
    {
        return $this->typeName === 'children';
    }

    public function isCustomForms(): bool
    {
        return $this->typeName === 'custom_forms';
    }

    public function isMultiple(): bool
    {
        return $this->typeName === 'multiple';
    }

    public function isMultiGeoTag(): bool
    {
        return $this->typeName === 'multi_geotag';
    }

    public function isJson(): bool
    {
        return $this->typeName === 'json';
    }

    public function isYaml(): bool
    {
        return $this->typeName === 'yaml';
    }

    public function isCss(): bool
    {
        return $this->typeName === 'css';
    }

    public function isManyToMany(): bool
    {
        return $this->typeName === 'many_to_many';
    }

    public function isManyToOne(): bool
    {
        return $this->typeName === 'many_to_one';
    }

    public function isCountry(): bool
    {
        return $this->typeName === 'country';
    }

    public function isSingleProvider(): bool
    {
        return $this->typeName === 'single_provider';
    }

    public function isMultiProvider(): bool
    {
        return $this->typeName === 'multi_provider';
    }

    public function isCollection(): bool
    {
        return $this->typeName === 'collection';
    }

    /**
     * @return array
     */
    public function getSerializationGroups(): array
    {
        return $this->serializationGroups;
    }

    /**
     * @param array $serializationGroups
     * @return NodeTypeField
     */
    public function setSerializationGroups(array $serializationGroups): NodeTypeField
    {
        $this->serializationGroups = $serializationGroups;
        return $this;
    }

    /**
     * @return bool
     */
    public function isExcludedFromSerialization(): bool
    {
        return $this->excludedFromSerialization;
    }

    /**
     * @param bool $excludedFromSerialization
     * @return NodeTypeField
     */
    public function setExcludedFromSerialization(bool $excludedFromSerialization): NodeTypeField
    {
        $this->excludedFromSerialization = $excludedFromSerialization;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSerializationExclusionExpression(): ?string
    {
        return $this->serializationExclusionExpression;
    }

    /**
     * @param string|null $serializationExclusionExpression
     * @return NodeTypeField
     */
    public function setSerializationExclusionExpression(?string $serializationExclusionExpression): NodeTypeField
    {
        $this->serializationExclusionExpression = $serializationExclusionExpression;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getSerializationMaxDepth(): ?int
    {
        return $this->serializationMaxDepth;
    }

    /**
     * @param int|null $serializationMaxDepth
     * @return NodeTypeField
     */
    public function setSerializationMaxDepth(?int $serializationMaxDepth): NodeTypeField
    {
        $this->serializationMaxDepth = $serializationMaxDepth;
        return $this;
    }
}
