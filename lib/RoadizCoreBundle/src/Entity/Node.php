<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use ApiPlatform\Doctrine\Orm\Filter as BaseFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Loggable;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\Core\AbstractEntities\LeafInterface;
use RZ\Roadiz\Core\AbstractEntities\LeafTrait;
use RZ\Roadiz\Core\AbstractEntities\NodeInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Api\Filter as RoadizFilter;
use RZ\Roadiz\CoreBundle\Model\AttributableInterface;
use RZ\Roadiz\CoreBundle\Model\AttributableTrait;
use RZ\Roadiz\CoreBundle\Repository\NodeRepository;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Node entities are the central feature of Roadiz,
 * it describes a document-like object which can be inherited
 * with *NodesSources* to create complex data structures.
 */
#[
    ORM\Entity(repositoryClass: NodeRepository::class),
    ORM\Table(name: "nodes"),
    ORM\Index(columns: ["visible"]),
    ORM\Index(columns: ["status"]),
    ORM\Index(columns: ["locked"]),
    ORM\Index(columns: ["sterile"]),
    ORM\Index(columns: ["position"]),
    ORM\Index(columns: ["created_at"]),
    ORM\Index(columns: ["updated_at"]),
    ORM\Index(columns: ["hide_children"]),
    ORM\Index(columns: ["home"]),
    ORM\Index(columns: ["node_name", "status"]),
    ORM\Index(columns: ["visible", "status"]),
    ORM\Index(columns: ["visible", "status", "parent_node_id"], name: "node_visible_status_parent"),
    ORM\Index(columns: ["status", "parent_node_id"], name: "node_status_parent"),
    ORM\Index(columns: ["nodeType_id", "status", "parent_node_id"], name: "node_nodetype_status_parent"),
    ORM\Index(columns: ["nodeType_id", "status", "parent_node_id", "position"], name: "node_nodetype_status_parent_position"),
    ORM\Index(columns: ["visible", "parent_node_id"], name: "node_visible_parent"),
    ORM\Index(columns: ["parent_node_id", "position"], name: "node_parent_position"),
    ORM\Index(columns: ["visible", "parent_node_id", "position"], name: "node_visible_parent_position"),
    ORM\Index(columns: ["status", "visible", "parent_node_id", "position"], name: "node_status_visible_parent_position"),
    ORM\HasLifecycleCallbacks,
    Gedmo\Loggable(logEntryClass: UserLogEntry::class),
    // Need to override repository method to see all nodes
    UniqueEntity(
        fields: 'nodeName',
        message: 'nodeName.alreadyExists',
        repositoryMethod: 'findOneWithoutSecurity'
    ),
    ApiFilter(PropertyFilter::class)
]
class Node extends AbstractDateTimedPositioned implements LeafInterface, AttributableInterface, Loggable, NodeInterface
{
    use LeafTrait;
    use AttributableTrait;

    public const DRAFT = 10;
    public const PENDING = 20;
    public const PUBLISHED = 30;
    public const ARCHIVED = 40;
    public const DELETED = 50;

    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    public static array $orderingFields = [
        'position' => 'position',
        'nodeName' => 'nodeName',
        'createdAt' => 'createdAt',
        'updatedAt' => 'updatedAt',
        'publishedAt' => 'ns.publishedAt',
    ];

    #[ORM\Column(name: 'node_name', type: 'string', length: 255, unique: true)]
    #[SymfonySerializer\Groups(['nodes_sources', 'nodes_sources_base', 'node', 'log_sources'])]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_base', 'node', 'log_sources'])]
    #[Serializer\Accessor(getter: "getNodeName", setter: "setNodeName")]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[ApiProperty(
        description: 'Unique node name (slug) used to build content URL',
        example: 'this-is-a-node-name',
    )]
    private string $nodeName = '';

    #[ORM\Column(name: 'dynamic_node_name', type: 'boolean', nullable: false, options: ['default' => true])]
    #[SymfonySerializer\Ignore]
    #[Gedmo\Versioned]
    private bool $dynamicNodeName = true;

    #[ORM\Column(name: 'home', type: 'boolean', nullable: false, options: ['default' => false])]
    #[SymfonySerializer\Ignore]
    private bool $home = false;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    #[SymfonySerializer\Groups(['nodes_sources_base', 'nodes_sources', 'node'])]
    #[Serializer\Groups(['nodes_sources_base', 'nodes_sources', 'node'])]
    #[Gedmo\Versioned]
    #[ApiProperty(
        description: 'Is this node visible in website navigation?',
        example: 'true',
    )]
    private bool $visible = true;

    /**
     * @internal You should use node Workflow to perform change on status.
     */
    #[ORM\Column(type: 'integer')]
    #[Serializer\Exclude]
    #[SymfonySerializer\Ignore]
    private int $status = Node::DRAFT;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    #[Assert\GreaterThanOrEqual(value: 0)]
    #[Assert\NotNull]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    #[Gedmo\Versioned]
    // @phpstan-ignore-next-line
    private ?int $ttl = 0;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    #[SymfonySerializer\Groups(['node'])]
    #[Serializer\Groups(['node'])]
    #[Gedmo\Versioned]
    #[ApiProperty(
        description: 'Is this node locked to prevent deletion and renaming?',
        example: 'false',
    )]
    private bool $locked = false;

    #[ORM\Column(name: 'hide_children', type: 'boolean', nullable: false, options: ['default' => false])]
    #[SymfonySerializer\Groups(['node'])]
    #[Serializer\Groups(['node'])]
    #[Gedmo\Versioned]
    #[ApiProperty(
        description: 'Does this node act as a container for other nodes?',
        example: 'false',
    )]
    private bool $hideChildren = false;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    #[SymfonySerializer\Groups(['node'])]
    #[Serializer\Groups(['node'])]
    #[Gedmo\Versioned]
    #[ApiProperty(
        description: 'Can this node hold other nodes inside?',
        example: 'false',
    )]
    private bool $sterile = false;

    #[ORM\Column(name: 'children_order', type: 'string', length: 50)]
    #[SymfonySerializer\Groups(['node', 'node_listing'])]
    #[Serializer\Groups(['node', 'node_listing'])]
    #[Assert\Length(max: 50)]
    #[Gedmo\Versioned]
    #[ApiProperty(
        description: 'This node children will be sorted by a given field',
        example: 'position',
        schema: [
            'type' => 'string',
            'enum' => ['position', 'nodeName', 'createdAt', 'updatedAt', 'publishedAt'],
            'example' => 'position'
        ],
    )]
    private string $childrenOrder = 'position';

    #[ORM\Column(name: 'children_order_direction', type: 'string', length: 4)]
    #[SymfonySerializer\Groups(['node', 'node_listing'])]
    #[Serializer\Groups(['node', 'node_listing'])]
    #[Assert\Length(max: 4)]
    #[Assert\Choice(choices: ['ASC', 'DESC'])]
    #[Gedmo\Versioned]
    #[ApiProperty(
        description: 'This node children will be sorted ascendant or descendant',
        example: 'ASC',
        schema: [
            'type' => 'string',
            'enum' => ['ASC', 'DESC'],
            'example' => 'ASC'
        ],
    )]
    private string $childrenOrderDirection = 'ASC';

    #[ORM\ManyToOne(targetEntity: NodeTypeInterface::class)]
    #[ORM\JoinColumn(name: 'nodeType_id', referencedColumnName: 'id', nullable:false, onDelete: 'CASCADE')]
    #[SymfonySerializer\Groups(['node'])]
    #[Serializer\Groups(['node'])]
    #[SymfonySerializer\Ignore]
    private NodeTypeInterface $nodeType;

    /**
     * @var Node|null
     */
    #[ORM\ManyToOne(targetEntity: Node::class, fetch: 'EAGER', inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_node_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    private ?LeafInterface $parent = null;

    /**
     * @var Collection<int, Node>
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Node::class, orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[SymfonySerializer\Groups(['node_children'])]
    #[Serializer\Groups(['node_children'])]
    private Collection $children;

    /**
     * @var Collection<int, NodesTags>
     */
    #[ORM\OneToMany(
        mappedBy: 'node',
        targetEntity: NodesTags::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    #[ApiFilter(BaseFilter\SearchFilter::class, properties: [
        "nodesTags.tag" => "exact",
        "nodesTags.tag.tagName" => "exact",
    ])]
    #[ApiFilter(RoadizFilter\NotFilter::class, properties: [
        "nodesTags.tag.tagName",
    ])]
    # Use IntersectionFilter after SearchFilter!
    #[ApiFilter(RoadizFilter\IntersectionFilter::class, properties: [
        "nodesTags.tag",
        "nodesTags.tag.tagName",
    ])]
    private Collection $nodesTags;

    /**
     * @var Collection<int, NodesCustomForms>
     */
    #[ORM\OneToMany(mappedBy: 'node', targetEntity: NodesCustomForms::class, fetch: 'EXTRA_LAZY')]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    private Collection $customForms;

    /**
     * @var Collection<int, NodeType>
     */
    #[ORM\JoinTable(name: 'stack_types')]
    #[ORM\InverseJoinColumn(name: 'nodetype_id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: NodeType::class)]
    #[Serializer\Groups(['node'])]
    #[SymfonySerializer\Groups(['node'])]
    #[SymfonySerializer\Ignore]
    private Collection $stackTypes;

    /**
     * @var Collection<int, NodesSources>
     */
    #[ORM\OneToMany(
        mappedBy: 'node',
        targetEntity: NodesSources::class,
        fetch: 'EXTRA_LAZY',
        orphanRemoval: true
    )]
    #[Serializer\Groups(['node'])]
    #[SymfonySerializer\Groups(['node'])]
    #[SymfonySerializer\Ignore]
    private Collection $nodeSources;

    /**
     * @var Collection<int, NodesToNodes>
     */
    #[ORM\OneToMany(
        mappedBy: 'nodeA',
        targetEntity: NodesToNodes::class,
        cascade: ['persist'],
        fetch: 'LAZY',
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    private Collection $bNodes;

    /**
     * @var Collection<int, NodesToNodes>
     */
    #[ORM\OneToMany(mappedBy: 'nodeB', targetEntity: NodesToNodes::class)]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    private Collection $aNodes;

    /**
     * @var Collection<int, AttributeValue>
     */
    #[ORM\OneToMany(mappedBy: 'node', targetEntity: AttributeValue::class, orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[Serializer\Groups(['node_attributes'])]
    #[SymfonySerializer\Groups(['node_attributes'])]
    #[SymfonySerializer\MaxDepth(1)]
    private Collection $attributeValues;

    /**
     * Create a new empty Node according to given node-type.
     */
    public function __construct()
    {
        $this->nodesTags = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->nodeSources = new ArrayCollection();
        $this->stackTypes = new ArrayCollection();
        $this->customForms = new ArrayCollection();
        $this->aNodes = new ArrayCollection();
        $this->bNodes = new ArrayCollection();
        $this->attributeValues = new ArrayCollection();
        $this->initAbstractDateTimed();
    }

    /**
     * @param int $status
     * @return string
     */
    public static function getStatusLabel($status): string
    {
        $nodeStatuses = [
            static::DRAFT => 'draft',
            static::PENDING => 'pending',
            static::PUBLISHED => 'published',
            static::ARCHIVED => 'archived',
            static::DELETED => 'deleted',
        ];

        if (isset($nodeStatuses[$status])) {
            return $nodeStatuses[$status];
        }

        throw new \InvalidArgumentException('Status does not exist.');
    }

    /**
     * Dynamic node name will be updated against default
     * translated nodeSource title at each save.
     *
     * Disable this parameter if you need to protect your nodeName
     * from title changes.
     *
     * @return bool
     */
    public function isDynamicNodeName(): bool
    {
        return $this->dynamicNodeName;
    }

    /**
     * @param bool $dynamicNodeName
     * @return $this
     */
    public function setDynamicNodeName(bool $dynamicNodeName): Node
    {
        $this->dynamicNodeName = (bool) $dynamicNodeName;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHome(): bool
    {
        return $this->home;
    }

    /**
     * @param bool $home
     * @return $this
     */
    public function setHome(bool $home): Node
    {
        $this->home = $home;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int|string $status Workflow only use <string> marking places
     * @return $this
     * @internal You should use node Workflow to perform change on status.
     */
    public function setStatus(int|string $status): Node
    {
        $this->status = (int) $status;
        return $this;
    }

    /**
     * @return int
     */
    public function getTtl(): int
    {
        return $this->ttl ?? 0;
    }

    /**
     * @param int|null $ttl
     *
     * @return Node
     */
    public function setTtl(?int $ttl): Node
    {
        $this->ttl = $ttl;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPublished(): bool
    {
        return ($this->status === Node::PUBLISHED);
    }

    /**
     * @return bool
     */
    public function isPending(): bool
    {
        return ($this->status === Node::PENDING);
    }

    /**
     * @return bool
     */
    public function isDraft(): bool
    {
        return ($this->status === Node::DRAFT);
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return ($this->status === Node::DELETED);
    }

    /**
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->locked;
    }

    /**
     * @param bool $locked
     * @return $this
     */
    public function setLocked(bool $locked): static
    {
        $this->locked = $locked;
        return $this;
    }

    /**
     * @return bool
     */
    public function getHideChildren(): bool
    {
        return $this->hideChildren;
    }

    /**
     * @param bool $hideChildren
     * @return $this
     */
    public function setHideChildren(bool $hideChildren): static
    {
        $this->hideChildren = $hideChildren;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHidingChildren(): bool
    {
        return $this->hideChildren;
    }

    /**
     * @param bool $hideChildren
     *
     * @return $this
     */
    public function setHidingChildren(bool $hideChildren): static
    {
        $this->hideChildren = $hideChildren;
        return $this;
    }

    /**
     * @return bool
     */
    public function isArchived(): bool
    {
        return ($this->status === Node::ARCHIVED);
    }

    /**
     * @return bool
     */
    public function isSterile(): bool
    {
        return $this->sterile;
    }

    /**
     * @param bool $sterile
     * @return $this
     */
    public function setSterile(bool $sterile): static
    {
        $this->sterile = $sterile;
        return $this;
    }

    /**
     * @return string
     */
    public function getChildrenOrder(): string
    {
        return $this->childrenOrder;
    }

    /**
     * @param string $childrenOrder
     * @return $this
     */
    public function setChildrenOrder(string $childrenOrder): static
    {
        $this->childrenOrder = $childrenOrder;
        return $this;
    }

    /**
     * @return string
     */
    public function getChildrenOrderDirection(): string
    {
        return $this->childrenOrderDirection;
    }

    /**
     * @param string $childrenOrderDirection
     * @return $this
     */
    public function setChildrenOrderDirection(string $childrenOrderDirection): static
    {
        $this->childrenOrderDirection = $childrenOrderDirection;
        return $this;
    }

    /**
     * @return Collection<int, NodesTags>
     */
    public function getNodesTags(): Collection
    {
        return $this->nodesTags;
    }

    /**
     * @param Collection<int, NodesTags> $nodesTags
     * @return $this
     */
    public function setNodesTags(Collection $nodesTags): static
    {
        foreach ($nodesTags as $singleNodesTags) {
            $singleNodesTags->setNode($this);
        }
        $this->nodesTags = $nodesTags;

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    #[SymfonySerializer\Groups(['nodes_sources', 'nodes_sources_base', 'node'])]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_base', 'node'])]
    #[Serializer\VirtualProperty]
    public function getTags(): Collection
    {
        return $this->nodesTags->map(function (NodesTags $nodesTags) {
            return $nodesTags->getTag();
        });
    }

    /**
     * @param iterable<Tag> $tags
     *
     * @return $this
     */
    public function setTags(iterable $tags): static
    {
        $this->nodesTags->clear();
        $i = 0;
        foreach ($tags as $tag) {
            $this->nodesTags->add(
                (new NodesTags())->setNode($this)->setTag($tag)->setPosition(++$i)
            );
        }
        return $this;
    }

    /**
     * @param Tag $tag
     *
     * @return $this
     */
    public function addTag(Tag $tag): static
    {
        if (
            !$this->getTags()->exists(function ($key, Tag $existingTag) use ($tag) {
                return $tag->getId() === $existingTag->getId();
            })
        ) {
            $last = $this->nodesTags->last();
            if (false !== $last) {
                $i = $last->getPosition();
            } else {
                $i = 0;
            }
            $this->nodesTags->add(
                (new NodesTags())->setNode($this)->setTag($tag)->setPosition(++$i)
            );
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        $nodeTags = $this->nodesTags->filter(function (NodesTags $existingNodesTags) use ($tag) {
            return $existingNodesTags->getTag()->getId() === $tag->getId();
        });
        foreach ($nodeTags as $singleNodeTags) {
            $this->nodesTags->removeElement($singleNodeTags);
        }

        return $this;
    }

    /**
     * @return Collection<int, NodesCustomForms>
     */
    public function getCustomForms(): Collection
    {
        return $this->customForms;
    }

    /**
     * @param Collection<int, NodesCustomForms> $customForms
     * @return $this
     */
    public function setCustomForms(Collection $customForms): static
    {
        $this->customForms = $customForms;
        return $this;
    }

    /**
     * Used by generated nodes-sources.
     *
     * @param NodesCustomForms $nodesCustomForms
     * @return $this
     */
    public function addCustomForm(NodesCustomForms $nodesCustomForms): static
    {
        if (!$this->customForms->contains($nodesCustomForms)) {
            $this->customForms->add($nodesCustomForms);
        }
        return $this;
    }

    /**
     * @param NodeType $stackType
     *
     * @return $this
     */
    public function removeStackType(NodeType $stackType): static
    {
        if ($this->getStackTypes()->contains($stackType)) {
            $this->getStackTypes()->removeElement($stackType);
        }

        return $this;
    }

    /**
     * @return Collection<int, NodeType>
     */
    public function getStackTypes(): Collection
    {
        return $this->stackTypes;
    }

    /**
     * @param NodeType $stackType
     *
     * @return $this
     */
    public function addStackType(NodeType $stackType): static
    {
        if (!$this->getStackTypes()->contains($stackType)) {
            $this->getStackTypes()->add($stackType);
        }

        return $this;
    }

    /**
     * Get node-sources using a given translation.
     *
     * @param TranslationInterface $translation
     * @return Collection<int, NodesSources>
     */
    #[SymfonySerializer\Ignore]
    public function getNodeSourcesByTranslation(TranslationInterface $translation): Collection
    {
        return $this->nodeSources->filter(function (NodesSources $nodeSource) use ($translation) {
            return $nodeSource->getTranslation()->getLocale() === $translation->getLocale();
        });
    }

    /**
     * @param NodesSources $ns
     *
     * @return $this
     */
    public function removeNodeSources(NodesSources $ns): static
    {
        if ($this->getNodeSources()->contains($ns)) {
            $this->getNodeSources()->removeElement($ns);
        }

        return $this;
    }

    /**
     * @return Collection<int, NodesSources>
     */
    public function getNodeSources(): Collection
    {
        return $this->nodeSources;
    }

    /**
     * @param NodesSources $ns
     *
     * @return $this
     */
    public function addNodeSources(NodesSources $ns): static
    {
        if (!$this->getNodeSources()->contains($ns)) {
            $this->getNodeSources()->add($ns);
        }

        return $this;
    }

    /**
     * @param NodeTypeField $field
     *
     * @return Collection<int, NodesToNodes>
     */
    #[SymfonySerializer\Ignore]
    public function getBNodesByField(NodeTypeFieldInterface $field): Collection
    {
        $criteria = Criteria::create();
        $criteria->andWhere(Criteria::expr()->eq('fieldName', $field->getName()));
        $criteria->orderBy(['position' => 'ASC']);
        return $this->getBNodes()->matching($criteria);
    }

    /**
     * Return nodes related to this (B nodes).
     *
     * @return Collection<int, NodesToNodes>
     */
    public function getBNodes(): Collection
    {
        return $this->bNodes;
    }

    /**
     * @param Collection<int, NodesToNodes> $bNodes
     * @return $this
     */
    public function setBNodes(Collection $bNodes): static
    {
        $this->bNodes->clear();
        foreach ($bNodes as $bNode) {
            if (!$this->hasBNode($bNode)) {
                $this->addBNode($bNode);
            }
        }
        return $this;
    }

    public function hasBNode(NodesToNodes $bNode): bool
    {
        return $this->getBNodes()->exists(function ($key, NodesToNodes $element) use ($bNode) {
            return $bNode->getNodeB()->getId() !== null &&
                $element->getNodeB()->getId() === $bNode->getNodeB()->getId() &&
                $element->getFieldName() === $bNode->getFieldName();
        });
    }

    /**
     * @param NodesToNodes $bNode
     * @return $this
     */
    public function addBNode(NodesToNodes $bNode): static
    {
        if (!$this->getBNodes()->contains($bNode)) {
            $this->getBNodes()->add($bNode);
            $bNode->setNodeA($this);
        }
        return $this;
    }

    public function clearBNodesForField(NodeTypeFieldInterface $field): Node
    {
        $toRemoveCollection = $this->getBNodes()->filter(function (NodesToNodes $element) use ($field) {
            return $element->getFieldName() === $field->getName();
        });
        /** @var NodesToNodes $toRemove */
        foreach ($toRemoveCollection as $toRemove) {
            $this->getBNodes()->removeElement($toRemove);
        }
        return $this;
    }

    /**
     * Return nodes which own a relation with this (A nodes).
     *
     * @return Collection<int, NodesToNodes>
     */
    public function getANodes(): Collection
    {
        return $this->aNodes;
    }

    /**
     * @return string
     */
    #[SymfonySerializer\Ignore]
    public function getOneLineSummary(): string
    {
        return $this->getId() . " — " . $this->getNodeName() . " — " . $this->getNodeType()->getName() .
        " — Visible : " . ($this->isVisible() ? 'true' : 'false') . PHP_EOL;
    }

    /**
     * @return string
     */
    public function getNodeName(): string
    {
        return $this->nodeName;
    }

    /**
     * @param string $nodeName
     * @return $this
     */
    public function setNodeName(string $nodeName): static
    {
        $this->nodeName = StringHandler::slugify($nodeName);
        return $this;
    }

    public function getNodeType(): NodeTypeInterface
    {
        return $this->nodeType;
    }

    public function setNodeType(NodeTypeInterface $nodeType): Node
    {
        $this->nodeType = $nodeType;
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
    public function setVisible(bool $visible): Node
    {
        $this->visible = $visible;
        return $this;
    }

    /**
     * @return string
     */
    #[SymfonySerializer\Ignore]
    public function getOneLineSourceSummary(): string
    {
        $text = "Source " .
            (
                $this->getNodeSources()->first() ?
                $this->getNodeSources()->first()->getId() :
                ''
            ) .
            PHP_EOL;

        foreach ($this->getNodeType()->getFields() as $field) {
            $getterName = $field->getGetterName();
            $text .= '[' . $field->getLabel() . ']: ' .
                ($this->getNodeSources()->first() ? $this->getNodeSources()->first()->$getterName() : '') .
                PHP_EOL;
        }

        return $text;
    }

    /**
     * After clone method.
     *
     * Clone current node and ist relations.
     */
    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->home = false;
            $children = $this->getChildren();
            $this->children = new ArrayCollection();
            foreach ($children as $child) {
                $cloneChild = clone $child;
                $this->addChild($cloneChild);
            }

            /** @var NodesTags[] $nodesTags */
            $nodesTags = $this->nodesTags->toArray();
            if ($nodesTags !== null) {
                $this->nodesTags = new ArrayCollection();
                foreach ($nodesTags as $nodesTag) {
                    $this->addTag($nodesTag->getTag());
                }
            }
            $nodeSources = $this->getNodeSources();
            $this->nodeSources = new ArrayCollection();
            /** @var NodesSources $nodeSource */
            foreach ($nodeSources as $nodeSource) {
                $cloneNodeSource = clone $nodeSource;
                $cloneNodeSource->setNode($this);
            }

            $attributeValues = $this->getAttributeValues();
            $this->attributeValues = new ArrayCollection();
            /** @var AttributeValue $attributeValue */
            foreach ($attributeValues as $attributeValue) {
                $cloneAttributeValue = clone $attributeValue;
                $cloneAttributeValue->setNode($this);
                $this->addAttributeValue($cloneAttributeValue);
            }

            // Get a random string after node-name.
            // This is for safety reasons
            // NodeDuplicator service will override it
            $nodeSource = $this->getNodeSources()->first();
            if ($nodeSource !== false) {
                $namePrefix = $nodeSource->getTitle() != "" ?
                    $nodeSource->getTitle() :
                    $this->nodeName;
            } else {
                $namePrefix = $this->nodeName;
            }
            $this->setNodeName($namePrefix . "-" . uniqid());
            $this->setCreatedAt(new \DateTime());
            $this->setUpdatedAt(new \DateTime());
        }
    }

    public function setParent(?LeafInterface $parent = null): static
    {
        if ($parent === $this) {
            throw new \InvalidArgumentException('An entity cannot have itself as a parent.');
        }
        if (null !== $parent && !($parent instanceof Node)) {
            throw new \InvalidArgumentException('A node can only have a Node as a parent.');
        }
        $this->parent = $parent;
        $this->parent?->addChild($this);

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
