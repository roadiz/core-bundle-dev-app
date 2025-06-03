<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\Contracts\NodeType\SerializableInterface;
use RZ\Roadiz\CoreBundle\Enum\FieldType;
use RZ\Roadiz\CoreBundle\Form\Constraint as RoadizAssert;
use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * NodeTypeField entities are used to create NodeTypes with
 * custom data structure.
 */
#[RoadizAssert\NodeTypeField]
final class NodeTypeField extends AbstractField implements NodeTypeFieldInterface, SerializableInterface
{
    #[
        Serializer\Groups(['node_type', 'node_type:import', 'setting']),
        Assert\Length(max: 50),
        RoadizAssert\NonSqlReservedWord(),
        RoadizAssert\SimpleLatinString()
    ]
    protected string $name;

    /**
     * If current field data should be the same over translations or not.
     */
    #[
        Serializer\Groups(['node_type', 'node_type:import']),
    ]
    private bool $universal = false;

    /**
     * Exclude current field from full-text search engines.
     */
    #[
        Serializer\Groups(['node_type', 'node_type:import']),
    ]
    private bool $excludeFromSearch = false;

    #[
        Serializer\Ignore
    ]
    private NodeTypeInterface $nodeType;

    #[
        Serializer\Groups(['node_type', 'node_type:import']),
    ]
    private ?string $serializationExclusionExpression = null;

    #[
        Serializer\Groups(['node_type', 'node_type:import']),
    ]
    private ?array $serializationGroups = null;

    #[
        Serializer\Groups(['node_type', 'node_type:import']),
    ]
    private ?array $normalizationContext = null;

    #[
        Serializer\Groups(['node_type', 'node_type:import']),
    ]
    private ?int $serializationMaxDepth = null;

    #[
        Serializer\Groups(['node_type', 'node_type:import']),
    ]
    private bool $excludedFromSerialization = false;

    #[
        Serializer\Groups(['node_type', 'node_type:import']),
    ]
    private ?int $minLength = null;

    #[
        Serializer\Groups(['node_type', 'node_type:import']),
    ]
    private ?int $maxLength = null;

    #[
        Serializer\Groups(['node_type', 'node_type:import']),
    ]
    private bool $indexed = false;

    #[
        Serializer\Groups(['node_type', 'node_type:import']),
    ]
    private bool $visible = true;

    #[
        Serializer\Groups(['node_type', 'node_type:import']),
    ]
    private bool $required = false;

    #[
        Serializer\Groups(['node_type'])
    ]
    public function getNodeTypeName(): string
    {
        return $this->getNodeType()->getName();
    }

    public function getNodeType(): NodeTypeInterface
    {
        return $this->nodeType;
    }

    public function setNodeType(NodeTypeInterface $nodeType): NodeTypeField
    {
        $this->nodeType = $nodeType;

        return $this;
    }

    public function getMinLength(): ?int
    {
        return $this->minLength;
    }

    public function setMinLength(?int $minLength): NodeTypeField
    {
        $this->minLength = $minLength;

        return $this;
    }

    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }

    public function setMaxLength(?int $maxLength): NodeTypeField
    {
        $this->maxLength = $maxLength;

        return $this;
    }

    /**
     * Tell if current field can be searched and indexed in a Search engine server.
     */
    public function isSearchable(): bool
    {
        return !$this->excludeFromSearch && in_array($this->getType(), FieldType::searchableTypes());
    }

    #[Serializer\Ignore]
    public function getOneLineSummary(): string
    {
        return $this->getId().' — '.$this->getLabel().' ['.$this->getName().']'.
        ' - '.$this->getTypeName().
        ($this->isIndexed() ? ' - indexed' : '').
        (!$this->isVisible() ? ' - hidden' : '').PHP_EOL;
    }

    /**
     * @return bool $isIndexed
     */
    public function isIndexed(): bool
    {
        // JSON types cannot be indexed
        return $this->indexed && 'json' !== $this->getDoctrineType();
    }

    public function setIndexed(bool $indexed): NodeTypeField
    {
        $this->indexed = $indexed;

        return $this;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): NodeTypeField
    {
        $this->visible = $visible;

        return $this;
    }

    public function isUniversal(): bool
    {
        return $this->universal;
    }

    /**
     * @see Same as isUniversal
     */
    public function getUniversal(): bool
    {
        return $this->universal;
    }

    public function setUniversal(bool $universal): NodeTypeField
    {
        $this->universal = $universal;

        return $this;
    }

    public function isExcludedFromSearch(): bool
    {
        return $this->getExcludeFromSearch();
    }

    public function getExcludeFromSearch(): bool
    {
        return $this->excludeFromSearch;
    }

    public function isExcludeFromSearch(): bool
    {
        return $this->getExcludeFromSearch();
    }

    public function setExcludeFromSearch(bool $excludeFromSearch): NodeTypeField
    {
        $this->excludeFromSearch = $excludeFromSearch;

        return $this;
    }

    public function getSerializationExclusionExpression(): ?string
    {
        return $this->serializationExclusionExpression;
    }

    public function setSerializationExclusionExpression(?string $serializationExclusionExpression): NodeTypeField
    {
        $this->serializationExclusionExpression = $serializationExclusionExpression;

        return $this;
    }

    public function getSerializationGroups(): array
    {
        return array_filter($this->serializationGroups ?? []);
    }

    public function setSerializationGroups(?array $serializationGroups): NodeTypeField
    {
        $this->serializationGroups = $serializationGroups;
        if (null !== $this->serializationGroups) {
            $this->serializationGroups = array_filter($this->serializationGroups);
        }
        if (empty($this->serializationGroups)) {
            $this->serializationGroups = null;
        }

        return $this;
    }

    public function getSerializationMaxDepth(): ?int
    {
        return $this->serializationMaxDepth;
    }

    public function setSerializationMaxDepth(?int $serializationMaxDepth): NodeTypeField
    {
        $this->serializationMaxDepth = $serializationMaxDepth;

        return $this;
    }

    public function isExcludedFromSerialization(): bool
    {
        return $this->excludedFromSerialization;
    }

    public function setExcludedFromSerialization(bool $excludedFromSerialization): NodeTypeField
    {
        $this->excludedFromSerialization = $excludedFromSerialization;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): NodeTypeField
    {
        $this->required = $required;

        return $this;
    }

    public function getNormalizationContext(): ?array
    {
        return $this->normalizationContext;
    }

    public function setNormalizationContext(?array $normalizationContext): NodeTypeField
    {
        $this->normalizationContext = $normalizationContext;

        return $this;
    }

    #[Serializer\Ignore]
    public function getNormalizationContextGroups(): ?array
    {
        return $this->normalizationContext['groups'] ?? [];
    }

    #[Serializer\Ignore]
    public function setNormalizationContextGroups(?array $normalizationContextGroups): NodeTypeField
    {
        if (null === $normalizationContextGroups) {
            $this->normalizationContext = null;
        }
        $this->normalizationContext['groups'] = $normalizationContextGroups;

        return $this;
    }
}
