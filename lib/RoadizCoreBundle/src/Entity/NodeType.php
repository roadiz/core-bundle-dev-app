<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\CoreBundle\Form\Constraint as RoadizAssert;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Component\Serializer\Attribute as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * NodeType describes each node structure family,
 * They are mandatory before creating any Node.
 */
final class NodeType implements NodeTypeInterface, \Stringable
{
    #[
        SymfonySerializer\Groups(['node_type:display', 'node_type', 'node_type:import', 'color']),
        Assert\Length(max: 7),
    ]
    protected ?string $color = '#000000';
    #[
        SymfonySerializer\Groups(['node_type:display', 'node_type', 'node_type:import', 'node']),
        Assert\NotNull(),
        Assert\NotBlank(),
        RoadizAssert\SimpleLatinString(),
        // Limit discriminator column to 30 characters for indexing optimization
        Assert\Length(max: 30)
    ]
    private string $name = '';
    #[
        SymfonySerializer\Groups(['node_type:display', 'node_type', 'node_type:import', 'node']),
        Assert\NotNull(),
        Assert\NotBlank(),
        Assert\Length(max: 250)
    ]
    private string $displayName = '';
    #[
        SymfonySerializer\Groups(['node_type', 'node_type:import']),
    ]
    private ?string $description = null;
    #[
        SymfonySerializer\Groups(['node_type:display', 'node_type', 'node_type:import']),
    ]
    private bool $visible = true;
    #[
        SymfonySerializer\Groups(['node_type', 'node_type:import']),
    ]
    private bool $publishable = false;

    /**
     * @var bool define if this node-type produces nodes that will have attributes
     */
    #[
        SymfonySerializer\Groups(['node_type', 'node_type:import']),
    ]
    private bool $attributable = false;
    #[
        SymfonySerializer\Groups(['node_type', 'node_type:import']),
    ]
    private bool $sortingAttributesByWeight = false;
    /**
     * Define if this node-type produces nodes that will be
     * viewable from a Controller.
     *
     * Typically, if a node has a URL.
     */
    #[
        SymfonySerializer\Groups(['node_type', 'node_type:import']),
    ]
    private bool $reachable = true;
    #[
        SymfonySerializer\Groups(['node_type', 'node_type:import']),
    ]
    private bool $hidingNodes = false;
    #[
        SymfonySerializer\Groups(['node_type', 'node_type:import']),
    ]
    private bool $hidingNonReachableNodes = false;
    /**
     * @var Collection<int, NodeTypeField>
     */
    #[
        SymfonySerializer\Groups(['node_type', 'node_type:import']),
    ]
    private Collection $fields;
    #[
        SymfonySerializer\Groups(['node_type', 'node_type:import']),
        Assert\GreaterThanOrEqual(value: 0),
        Assert\NotNull
    ]
    // @phpstan-ignore-next-line
    private ?int $defaultTtl = 0;
    /**
     * Define if this node-type title will be indexed during its parent indexation.
     */
    #[
        SymfonySerializer\Groups(['node_type', 'node_type:import']),
    ]
    private bool $searchable = true;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
        $this->name = 'Untitled';
        $this->displayName = 'Untitled node-type';
    }

    #[\Override]
    public function getLabel(): string
    {
        return $this->getDisplayName();
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * @return $this
     */
    public function setDisplayName(?string $displayName): NodeType
    {
        $this->displayName = $displayName ?? '';

        return $this;
    }

    #[\Override]
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return $this
     */
    public function setDescription(?string $description = null): NodeType
    {
        $this->description = $description;

        return $this;
    }

    #[\Override]
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * @return $this
     */
    public function setVisible(bool $visible): NodeType
    {
        $this->visible = $visible;

        return $this;
    }

    #[\Override]
    public function isPublishable(): bool
    {
        return $this->publishable;
    }

    public function setPublishable(bool $publishable): NodeType
    {
        $this->publishable = $publishable;

        return $this;
    }

    public function getReachable(): bool
    {
        return $this->reachable;
    }

    #[\Override]
    public function isReachable(): bool
    {
        return $this->getReachable();
    }

    public function setReachable(bool $reachable): NodeType
    {
        $this->reachable = $reachable;

        return $this;
    }

    public function isHidingNodes(): bool
    {
        return $this->hidingNodes;
    }

    /**
     * @return $this
     */
    public function setHidingNodes(bool $hidingNodes): NodeType
    {
        $this->hidingNodes = $hidingNodes;

        return $this;
    }

    public function isHidingNonReachableNodes(): bool
    {
        return $this->hidingNonReachableNodes;
    }

    public function setHidingNonReachableNodes(bool $hidingNonReachableNodes): NodeType
    {
        $this->hidingNonReachableNodes = $hidingNonReachableNodes;

        return $this;
    }

    /**
     * Gets the value of color.
     */
    #[\Override]
    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * Sets the value of color.
     *
     * @return $this
     */
    public function setColor(?string $color): NodeType
    {
        $this->color = $color;

        return $this;
    }

    public function getDefaultTtl(): int
    {
        return $this->defaultTtl ?? 0;
    }

    public function setDefaultTtl(?int $defaultTtl): NodeType
    {
        $this->defaultTtl = $defaultTtl;

        return $this;
    }

    #[\Override]
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
    #[\Override]
    public function getFields(): Collection
    {
        return $this->fields;
    }

    /**
     * @param Collection<int, NodeTypeField> $fields
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
     */
    #[SymfonySerializer\Ignore]
    #[\Override]
    public function getFieldsNames(): array
    {
        return array_map(fn (NodeTypeField $field) => $field->getName(), $this->getFields()->toArray());
    }

    #[\Override]
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName(?string $name): NodeType
    {
        $this->name = StringHandler::classify($name ?? '');

        return $this;
    }

    public function addField(NodeTypeField $field): NodeType
    {
        if (!$this->getFields()->contains($field)) {
            $this->getFields()->add($field);
            $field->setNodeType($this);
        }

        return $this;
    }

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
    #[\Override]
    public function getSourceEntityFullQualifiedClassName(): string
    {
        // @phpstan-ignore-next-line
        return static::getGeneratedEntitiesNamespace().'\\'.$this->getSourceEntityClassName();
    }

    #[SymfonySerializer\Ignore]
    public static function getGeneratedEntitiesNamespace(): string
    {
        return 'App\\GeneratedEntity';
    }

    /**
     * Get node-source entity class name without its namespace.
     */
    #[SymfonySerializer\Ignore]
    #[\Override]
    public function getSourceEntityClassName(): string
    {
        return 'NS'.ucwords($this->getName());
    }

    /**
     * Get node-source entity database table name.
     */
    #[SymfonySerializer\Ignore]
    #[\Override]
    public function getSourceEntityTableName(): string
    {
        return 'ns_'.\mb_strtolower($this->getName());
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * Get every searchable node-type fields as a Doctrine ArrayCollection.
     */
    #[SymfonySerializer\Ignore]
    #[\Override]
    public function getSearchableFields(): Collection
    {
        return $this->getFields()->filter(fn (NodeTypeField $field) => $field->isSearchable());
    }

    #[\Override]
    public function isSearchable(): bool
    {
        return $this->searchable;
    }

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

    public function __clone(): void
    {
        /** @var Collection<int, NodeTypeField> $fields */
        $fields = $this->getFields();
        $this->fields = new ArrayCollection();
        foreach ($fields as $field) {
            $cloneField = clone $field;
            $this->addField($cloneField);
        }
    }
}
