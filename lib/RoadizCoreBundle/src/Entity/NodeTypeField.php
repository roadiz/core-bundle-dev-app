<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\Contracts\NodeType\SerializableInterface;
use RZ\Roadiz\Core\AbstractEntities\AbstractField;
use RZ\Roadiz\CoreBundle\Form\Constraint as RoadizAssert;
use RZ\Roadiz\CoreBundle\Repository\NodeTypeFieldRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * NodeTypeField entities are used to create NodeTypes with
 * custom data structure.
 */
#[
    ORM\Entity(repositoryClass: NodeTypeFieldRepository::class),
    ORM\Table(name: "node_type_fields"),
    ORM\Index(columns: ["visible"]),
    ORM\Index(columns: ["indexed"]),
    ORM\Index(columns: ["position"]),
    ORM\Index(columns: ["group_name"]),
    ORM\Index(columns: ["group_name_canonical"]),
    ORM\Index(columns: ["type"]),
    ORM\Index(columns: ["universal"]),
    ORM\Index(columns: ["node_type_id", "position"], name: "ntf_type_position"),
    ORM\UniqueConstraint(columns: ["name", "node_type_id"]),
    ORM\HasLifecycleCallbacks,
    UniqueEntity(fields: ["name", "nodeType"]),
    RoadizAssert\NodeTypeField
]
class NodeTypeField extends AbstractField implements NodeTypeFieldInterface, SerializableInterface
{
    #[
        ORM\Column(type: "string", length: 250),
        Serializer\Expose,
        Serializer\Groups(["node_type", "setting"]),
        SymfonySerializer\Groups(["node_type", "setting"]),
        Assert\Length(max: 250),
        Serializer\Type("string"),
        RoadizAssert\NonSqlReservedWord(),
        RoadizAssert\SimpleLatinString()
    ]
    protected string $name;

    /**
     * If current field data should be the same over translations or not.
     */
    #[
        ORM\Column(name: "universal", type: "boolean", nullable: false, options: ["default" => false]),
        Serializer\Groups(["node_type"]),
        SymfonySerializer\Groups(["node_type"]),
        Serializer\Type("bool")
    ]
    private bool $universal = false;

    /**
     * Exclude current field from full-text search engines.
     */
    #[
        ORM\Column(name: "exclude_from_search", type: "boolean", nullable: false, options: ["default" => false]),
        Serializer\Groups(["node_type"]),
        SymfonySerializer\Groups(["node_type"]),
        Serializer\Type("bool")
    ]
    private bool $excludeFromSearch = false;

    #[
        ORM\ManyToOne(targetEntity: NodeType::class, inversedBy: "fields"),
        ORM\JoinColumn(name: "node_type_id", onDelete: "CASCADE"),
        Serializer\Exclude(),
        SymfonySerializer\Ignore
    ]
    private ?NodeTypeInterface $nodeType = null;

    #[
        Serializer\Groups(["node_type"]),
        SymfonySerializer\Groups(["node_type"]),
        Serializer\Type("string"),
        ORM\Column(name: "serialization_exclusion_expression", type: "text", nullable: true)
    ]
    private ?string $serializationExclusionExpression = null;

    #[
        Serializer\Groups(["node_type"]),
        SymfonySerializer\Groups(["node_type"]),
        Serializer\Type("array<string>"),
        ORM\Column(name: "serialization_groups", type: "json", nullable: true)
    ]
    private ?array $serializationGroups = null;

    #[
        Serializer\Groups(["node_type"]),
        SymfonySerializer\Groups(["node_type"]),
        Serializer\Type("int"),
        ORM\Column(name: "serialization_max_depth", type: "integer", nullable: true)
    ]
    private ?int $serializationMaxDepth = null;

    #[
        Serializer\Groups(["node_type"]),
        SymfonySerializer\Groups(["node_type"]),
        Serializer\Type("bool"),
        ORM\Column(name: "excluded_from_serialization", type: "boolean", nullable: false, options: ["default" => false])
    ]
    private bool $excludedFromSerialization = false;

    #[
        ORM\Column(name: "min_length", type: "integer", nullable: true),
        Serializer\Groups(["node_type"]),
        SymfonySerializer\Groups(["node_type"]),
        Serializer\Type("int")
    ]
    private ?int $minLength = null;

    #[
        ORM\Column(name: "max_length", type: "integer", nullable: true),
        Serializer\Groups(["node_type"]),
        SymfonySerializer\Groups(["node_type"]),
        Serializer\Type("int")
    ]
    private ?int $maxLength = null;

    #[
        ORM\Column(type: "boolean", nullable: false, options: ["default" => false]),
        Serializer\Groups(["node_type"]),
        SymfonySerializer\Groups(["node_type"]),
        Serializer\Type("bool")
    ]
    private bool $indexed = false;

    #[
        ORM\Column(type: "boolean", nullable: false, options: ["default" => true]),
        Serializer\Groups(["node_type"]),
        SymfonySerializer\Groups(["node_type"]),
        Serializer\Type("bool")
    ]
    private bool $visible = true;

    #[
        Serializer\VirtualProperty(),
        Serializer\Type("string"),
        Serializer\Groups(["node_type"]),
        SymfonySerializer\Groups(["node_type"])
    ]
    public function getNodeTypeName(): string
    {
        return $this->getNodeType() ? $this->getNodeType()->getName() : '';
    }

    /**
     * @return NodeTypeInterface|null
     */
    public function getNodeType(): ?NodeTypeInterface
    {
        return $this->nodeType;
    }

    /**
     * @param NodeTypeInterface|null $nodeType
     *
     * @return $this
     */
    public function setNodeType(?NodeTypeInterface $nodeType)
    {
        $this->nodeType = $nodeType;

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
     *
     * @return $this
     */
    public function setMinLength(?int $minLength)
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
     *
     * @return $this
     */
    public function setMaxLength(?int $maxLength)
    {
        $this->maxLength = $maxLength;

        return $this;
    }

    /**
     * Tell if current field can be searched and indexed in a Search engine server.
     *
     * @return bool
     */
    public function isSearchable(): bool
    {
        return !$this->excludeFromSearch && in_array($this->getType(), static::$searchableTypes);
    }

    /**
     * @return string
     */
    #[SymfonySerializer\Ignore]
    public function getOneLineSummary()
    {
        return $this->getId() . " — " . $this->getLabel() . ' [' . $this->getName() . ']' .
        ' - ' . $this->getTypeName() .
        ($this->isIndexed() ? ' - indexed' : '') .
        (!$this->isVisible() ? ' - hidden' : '') . PHP_EOL;
    }

    /**
     * @return boolean $isIndexed
     */
    public function isIndexed(): bool
    {
        // JSON types cannot be indexed
        return $this->indexed && $this->getDoctrineType() !== 'json';
    }

    /**
     * @param bool $indexed
     * @return $this
     */
    public function setIndexed(bool $indexed)
    {
        $this->indexed = $indexed;
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
     * @return $this
     */
    public function setVisible(bool $visible)
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
     * @see Same as isUniversal
     * @return bool
     */
    public function getUniversal(): bool
    {
        return $this->universal;
    }

    /**
     * @param bool $universal
     * @return NodeTypeField
     */
    public function setUniversal(bool $universal)
    {
        $this->universal = $universal;
        return $this;
    }

    /**
     * @return bool
     */
    public function isExcludedFromSearch(): bool
    {
        return $this->getExcludeFromSearch();
    }

    /**
     * @return bool
     */
    public function getExcludeFromSearch(): bool
    {
        return $this->excludeFromSearch;
    }

    /**
     * @return bool
     */
    public function isExcludeFromSearch(): bool
    {
        return $this->getExcludeFromSearch();
    }

    /**
     * @param bool $excludeFromSearch
     *
     * @return NodeTypeField
     */
    public function setExcludeFromSearch(bool $excludeFromSearch)
    {
        $this->excludeFromSearch = $excludeFromSearch;

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
     * @return array
     */
    public function getSerializationGroups(): array
    {
        return array_filter($this->serializationGroups ?? []);
    }

    /**
     * @param array|null $serializationGroups
     * @return NodeTypeField
     */
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
}
