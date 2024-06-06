<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\CoreBundle\Form\Constraint as RoadizAssert;
use RZ\Roadiz\CoreBundle\Repository\NodeTypeRepository;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * NodeType describes each node structure family,
 * They are mandatory before creating any Node.
 */
#[
    ORM\Entity(repositoryClass: NodeTypeRepository::class),
    ORM\Table(name: "node_types"),
    ORM\Index(columns: ["name"], name: "node_type_name"),
    ORM\Index(columns: ["visible"]),
    ORM\Index(columns: ["publishable"]),
    ORM\Index(columns: ["attributable"]),
    ORM\Index(columns: ["hiding_nodes"]),
    ORM\Index(columns: ["hiding_non_reachable_nodes"]),
    ORM\Index(columns: ["reachable"]),
    ORM\Index(columns: ["searchable"], name: "nt_searchable"),
    UniqueEntity(fields: ["name"]),
    UniqueEntity(fields: ["displayName"])
]
class NodeType extends AbstractEntity implements NodeTypeInterface
{
    #[
        ORM\Column(name: "color", type: "string", length: 7, unique: false, nullable: true),
        Serializer\Groups(["node_type", "color"]),
        SymfonySerializer\Groups(["node_type", "color"]),
        Serializer\Type("string"),
        Assert\Length(max: 7),
    ]
    protected ?string $color = '#000000';
    #[
        ORM\Column(type: "string", length: 30, unique: true),
        Serializer\Groups(["node_type", "node"]),
        SymfonySerializer\Groups(["node_type", "node"]),
        Serializer\Type("string"),
        Assert\NotNull(),
        Assert\NotBlank(),
        RoadizAssert\NonSqlReservedWord(),
        RoadizAssert\SimpleLatinString(),
        // Limit discriminator column to 30 characters for indexing optimization
        Assert\Length(max: 30)
    ]
    private string $name = '';
    #[
        ORM\Column(name: "display_name", type: "string", length: 250),
        Serializer\Groups(["node_type", "node"]),
        SymfonySerializer\Groups(["node_type", "node"]),
        Serializer\Type("string"),
        Assert\NotNull(),
        Assert\NotBlank(),
        Assert\Length(max: 250)
    ]
    private string $displayName = '';
    #[
        ORM\Column(type: "text", nullable: true),
        Serializer\Groups(["node_type"]),
        SymfonySerializer\Groups(["node_type"]),
        Serializer\Type("string")
    ]
    private ?string $description = null;
    #[
        ORM\Column(type: "boolean", nullable: false, options: ["default" => true]),
        Serializer\Groups(["node_type"]),
        SymfonySerializer\Groups(["node_type"]),
        Serializer\Type("boolean")
    ]
    private bool $visible = true;
    #[
        ORM\Column(type: "boolean", nullable: false, options: ["default" => false]),
        Serializer\Groups(["node_type"]),
        SymfonySerializer\Groups(["node_type"]),
        Serializer\Type("boolean")
    ]
    private bool $publishable = false;

    /**
     * @var bool Define if this node-type produces nodes that will have attributes.
     */
    #[
        ORM\Column(type: "boolean", nullable: false, options: ["default" => true]),
        Serializer\Groups(["node_type"]),
        SymfonySerializer\Groups(["node_type"]),
        Serializer\Type("boolean")
    ]
    private bool $attributable = false;
    #[
        ORM\Column(name: "attributable_by_weight", type: "boolean", nullable: false, options: ["default" => false]),
        Serializer\Groups(["node_type"]),
        SymfonySerializer\Groups(["node_type"]),
        Serializer\Type("boolean")
    ]
    private bool $sortingAttributesByWeight = false;
    /**
     * Define if this node-type produces nodes that will be
     * viewable from a Controller.
     *
     * Typically, if a node has a URL.
     */
    #[
        ORM\Column(name: "reachable", type: "boolean", nullable: false, options: ["default" => true]),
        Serializer\Groups(["node_type"]),
        SymfonySerializer\Groups(["node_type"]),
        Serializer\Type("boolean")
    ]
    private bool $reachable = true;
    #[
        ORM\Column(name: "hiding_nodes", type: "boolean", nullable: false, options: ["default" => false]),
        Serializer\Groups(["node_type"]),
        SymfonySerializer\Groups(["node_type"]),
        Serializer\Type("boolean")
    ]
    private bool $hidingNodes = false;
    #[
        ORM\Column(name: "hiding_non_reachable_nodes", type: "boolean", nullable: false, options: ["default" => false]),
        Serializer\Groups(["node_type"]),
        SymfonySerializer\Groups(["node_type"]),
        Serializer\Type("boolean")
    ]
    private bool $hidingNonReachableNodes = false;
    /**
     * @var Collection<int, NodeTypeField>
     */
    #[
        ORM\OneToMany(
            mappedBy: "nodeType",
            targetEntity: NodeTypeField::class,
            cascade: ["all"],
            orphanRemoval: true
        ),
        ORM\OrderBy(["position" => "ASC"]),
        Serializer\Groups(["node_type"]),
        SymfonySerializer\Groups(["node_type"]),
        Serializer\Type("ArrayCollection<RZ\Roadiz\CoreBundle\Entity\NodeTypeField>"),
        Serializer\Accessor(getter: "getFields", setter: "setFields")
    ]
    private Collection $fields;
    #[
        ORM\Column(name: "default_ttl", type: "integer", nullable: false, options: ["default" => 0]),
        Serializer\Groups(["node_type"]),
        SymfonySerializer\Groups(["node_type"]),
        Serializer\Type("int"),
        Assert\GreaterThanOrEqual(value: 0),
        Assert\NotNull
    ]
    // @phpstan-ignore-next-line
    private ?int $defaultTtl = 0;
    /**
     * Define if this node-type title will be indexed during its parent indexation.
     */
    #[
        ORM\Column(name: "searchable", type: "boolean", nullable: false, options: ["default" => true]),
        Serializer\Groups(["node_type"]),
        SymfonySerializer\Groups(["node_type"]),
        Serializer\Type("boolean")
    ]
    private bool $searchable = true;

    /**
     * Create a new NodeType.
     */
    public function __construct()
    {
        $this->fields = new ArrayCollection();
        $this->name = 'Untitled';
        $this->displayName = 'Untitled node-type';
    }

    public function getLabel(): string
    {
        return $this->getDisplayName();
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * @param string|null $displayName
     *
     * @return $this
     */
    public function setDisplayName(?string $displayName): NodeType
    {
        $this->displayName = $displayName ?? '';
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return $this
     */
    public function setDescription(?string $description = null): NodeType
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * @param boolean $visible
     * @return $this
     */
    public function setVisible(bool $visible): NodeType
    {
        $this->visible = $visible;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPublishable(): bool
    {
        return $this->publishable;
    }

    /**
     * @param bool $publishable
     * @return NodeType
     */
    public function setPublishable(bool $publishable): NodeType
    {
        $this->publishable = $publishable;
        return $this;
    }

    /**
     * @return bool
     */
    public function getReachable(): bool
    {
        return $this->reachable;
    }

    /**
     * @return bool
     */
    public function isReachable(): bool
    {
        return $this->getReachable();
    }

    /**
     * @param bool $reachable
     * @return NodeType
     */
    public function setReachable(bool $reachable): NodeType
    {
        $this->reachable = $reachable;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isHidingNodes(): bool
    {
        return $this->hidingNodes;
    }

    /**
     * @param boolean $hidingNodes
     *
     * @return $this
     */
    public function setHidingNodes(bool $hidingNodes): NodeType
    {
        $this->hidingNodes = $hidingNodes;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHidingNonReachableNodes(): bool
    {
        return $this->hidingNonReachableNodes;
    }

    /**
     * @param bool $hidingNonReachableNodes
     *
     * @return NodeType
     */
    public function setHidingNonReachableNodes(bool $hidingNonReachableNodes): NodeType
    {
        $this->hidingNonReachableNodes = $hidingNonReachableNodes;
        return $this;
    }

    /**
     * Gets the value of color.
     *
     * @return string|null
     */
    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * Sets the value of color.
     *
     * @param string|null $color
     *
     * @return $this
     */
    public function setColor(?string $color): NodeType
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return int
     */
    public function getDefaultTtl(): int
    {
        return $this->defaultTtl ?? 0;
    }

    /**
     * @param int|null $defaultTtl
     *
     * @return NodeType
     */
    public function setDefaultTtl(?int $defaultTtl): NodeType
    {
        $this->defaultTtl = $defaultTtl;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return NodeTypeField|null
     */
    public function getFieldByName(string $name): ?NodeTypeField
    {
        $fieldCriteria = Criteria::create();
        $fieldCriteria->andWhere(Criteria::expr()->eq('name', $name));
        $fieldCriteria->setMaxResults(1);
        $field = $this->getFields()->matching($fieldCriteria)->first();
        return $field ?: null;
    }

    /**
     * @return Collection<int, NodeTypeField>
     */
    public function getFields(): Collection
    {
        return $this->fields;
    }

    /**
     * @param Collection<int, NodeTypeField> $fields
     *
     * @return NodeType
     */
    public function setFields(Collection $fields): NodeType
    {
        $this->fields = $fields;
        foreach ($this->fields as $field) {
            $field->setNodeType($this);
        }

        return $this;
    }

    /**
     * Get every node-type fields names in
     * a simple array.
     *
     * @return array
     */
    #[SymfonySerializer\Ignore]
    public function getFieldsNames(): array
    {
        return array_map(function (NodeTypeField $field) {
            return $field->getName();
        }, $this->getFields()->toArray());
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return $this
     */
    public function setName(?string $name): NodeType
    {
        $this->name = StringHandler::classify($name ?? '');
        return $this;
    }

    /**
     * @param NodeTypeField $field
     *
     * @return NodeType
     */
    public function addField(NodeTypeField $field): NodeType
    {
        if (!$this->getFields()->contains($field)) {
            $this->getFields()->add($field);
            $field->setNodeType($this);
        }

        return $this;
    }

    /**
     * @param NodeTypeField $field
     *
     * @return NodeType
     */
    public function removeField(NodeTypeField $field): NodeType
    {
        if ($this->getFields()->contains($field)) {
            $this->getFields()->removeElement($field);
        }

        return $this;
    }

    /**
     * @return class-string<NodesSources>
     */
    #[SymfonySerializer\Ignore]
    public function getSourceEntityFullQualifiedClassName(): string
    {
        // @phpstan-ignore-next-line
        return static::getGeneratedEntitiesNamespace() . '\\' . $this->getSourceEntityClassName();
    }

    /**
     * @return string
     */
    #[SymfonySerializer\Ignore]
    public static function getGeneratedEntitiesNamespace(): string
    {
        return 'App\\GeneratedEntity';
    }

    /**
     * Get node-source entity class name without its namespace.
     *
     * @return string
     */
    #[SymfonySerializer\Ignore]
    public function getSourceEntityClassName(): string
    {
        return 'NS' . ucwords($this->getName());
    }

    /**
     * Get node-source entity database table name.
     *
     * @return string
     */
    #[SymfonySerializer\Ignore]
    public function getSourceEntityTableName(): string
    {
        return 'ns_' . \mb_strtolower($this->getName());
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }

    /**
     * Get every searchable node-type fields as a Doctrine ArrayCollection.
     *
     * @return Collection
     */
    #[SymfonySerializer\Ignore]
    public function getSearchableFields(): Collection
    {
        return $this->getFields()->filter(function (NodeTypeField $field) {
            return $field->isSearchable();
        });
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
     * @return NodeType
     */
    public function setSearchable(bool $searchable): NodeType
    {
        $this->searchable = $searchable;
        return $this;
    }

    public function isAttributable(): bool
    {
        return $this->attributable;
    }

    public function setAttributable(bool $attributable): NodeType
    {
        $this->attributable = $attributable;
        return $this;
    }

    public function isSortingAttributesByWeight(): bool
    {
        return $this->sortingAttributesByWeight;
    }

    public function setSortingAttributesByWeight(bool $sortingAttributesByWeight): NodeType
    {
        $this->sortingAttributesByWeight = $sortingAttributesByWeight;
        return $this;
    }
}
