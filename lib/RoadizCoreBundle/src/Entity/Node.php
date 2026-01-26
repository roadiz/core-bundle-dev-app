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
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Loggable;
use Gedmo\Mapping\Annotation as Gedmo;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Core\AbstractEntities\DateTimedInterface;
use RZ\Roadiz\Core\AbstractEntities\DateTimedTrait;
use RZ\Roadiz\Core\AbstractEntities\LeafInterface;
use RZ\Roadiz\Core\AbstractEntities\LeafTrait;
use RZ\Roadiz\Core\AbstractEntities\NodeInterface;
use RZ\Roadiz\Core\AbstractEntities\SequentialIdTrait;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Api\Filter as RoadizFilter;
use RZ\Roadiz\CoreBundle\Api\Filter\NodeTypePublishableFilter;
use RZ\Roadiz\CoreBundle\Api\Filter\NodeTypeReachableFilter;
use RZ\Roadiz\CoreBundle\Api\Filter\TagGroupFilter;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Model\AttributableInterface;
use RZ\Roadiz\CoreBundle\Model\AttributableTrait;
use RZ\Roadiz\CoreBundle\Repository\NodeRepository;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Node entities are the central feature of Roadiz,
 * it describes a document-like object which can be inherited
 * with *NodesSources* to create complex data structures.
 *
 * @implements LeafInterface<Node>
 */
#[ORM\Entity(repositoryClass: NodeRepository::class),
    ORM\Table(name: 'nodes'),
    ORM\Index(columns: ['visible']),
    ORM\Index(columns: ['status']),
    ORM\Index(columns: ['locked']),
    ORM\Index(columns: ['position']),
    ORM\Index(columns: ['created_at']),
    ORM\Index(columns: ['updated_at']),
    ORM\Index(columns: ['hide_children']),
    ORM\Index(columns: ['home']),
    ORM\Index(columns: ['shadow'], name: 'node_shadow'),
    ORM\Index(columns: ['node_name', 'status']),
    ORM\Index(columns: ['visible', 'status']),
    ORM\Index(columns: ['visible', 'status', 'parent_node_id'], name: 'node_visible_status_parent'),
    ORM\Index(columns: ['status', 'parent_node_id'], name: 'node_status_parent'),
    ORM\Index(columns: ['nodetype_name'], name: 'node_ntname'),
    ORM\Index(columns: ['nodetype_name', 'status'], name: 'node_ntname_status'),
    ORM\Index(columns: ['nodetype_name', 'status', 'parent_node_id'], name: 'node_ntname_status_parent'),
    ORM\Index(columns: ['nodetype_name', 'status', 'parent_node_id', 'position'], name: 'node_ntname_status_parent_position'),
    ORM\Index(columns: ['visible', 'parent_node_id'], name: 'node_visible_parent'),
    ORM\Index(columns: ['parent_node_id', 'position'], name: 'node_parent_position'),
    ORM\Index(columns: ['visible', 'parent_node_id', 'position'], name: 'node_visible_parent_position'),
    ORM\Index(columns: ['status', 'visible', 'parent_node_id', 'position'], name: 'node_status_visible_parent_position'),
    ORM\HasLifecycleCallbacks,
    Gedmo\Loggable(logEntryClass: UserLogEntry::class),
    // Need to override repository method to see all nodes
    UniqueEntity(
        fields: 'nodeName',
        message: 'nodeName.alreadyExists',
        repositoryMethod: 'findOneWithoutSecurity'
    ),
    ApiFilter(NodeTypeReachableFilter::class),
    ApiFilter(NodeTypePublishableFilter::class),
    ApiFilter(PropertyFilter::class),
    ApiFilter(TagGroupFilter::class)]
class Node implements DateTimedInterface, LeafInterface, AttributableInterface, Loggable, NodeInterface, \Stringable
{
    use SequentialIdTrait;
    use DateTimedTrait;
    use LeafTrait;
    use AttributableTrait;

    #[SymfonySerializer\Ignore]
    public static array $orderingFields = [
        'position' => 'position',
        'nodeName' => 'nodeName',
        'createdAt' => 'createdAt',
        'updatedAt' => 'updatedAt',
        'publishedAt' => 'ns.publishedAt',
    ];

    #[ORM\Column(name: 'node_name', type: 'string', length: 255, unique: true)]
    #[SymfonySerializer\Groups(['nodes_sources', 'nodes_sources_base', 'node', 'log_sources'])]
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

    /**
     * @var bool A shadow node is a node hidden from back-office node-trees and not publicly available. It is used to create a shadow root for nodes.
     */
    #[ORM\Column(name: 'shadow', type: 'boolean', nullable: false, options: ['default' => false])]
    #[SymfonySerializer\Ignore]
    private bool $shadow = false;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    #[SymfonySerializer\Groups(['nodes_sources_base', 'nodes_sources', 'node'])]
    #[Gedmo\Versioned]
    #[ApiProperty(
        description: 'Is this node visible in website navigation?',
        example: 'true',
    )]
    private bool $visible = true;

    /**
     * @internal you should use node Workflow to perform change on status
     */
    #[ORM\Column(
        name: 'status',
        type: Types::SMALLINT,
        enumType: NodeStatus::class,
        options: ['default' => NodeStatus::DRAFT]
    )]
    #[SymfonySerializer\Ignore]
    private NodeStatus $status = NodeStatus::DRAFT;

    #[ORM\Column(
        type: Types::INTEGER,
        nullable: false,
        options: ['default' => 0]
    )]
    #[Assert\GreaterThanOrEqual(value: 0)]
    #[Assert\NotNull]
    #[SymfonySerializer\Ignore]
    #[Gedmo\Versioned]
    // @phpstan-ignore-next-line
    private ?int $ttl = 0;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    #[SymfonySerializer\Groups(['node'])]
    #[Gedmo\Versioned]
    #[ApiProperty(
        description: 'Is this node locked to prevent deletion and renaming?',
        example: 'false',
    )]
    private bool $locked = false;

    #[ORM\Column(name: 'hide_children', type: 'boolean', nullable: false, options: ['default' => false])]
    #[SymfonySerializer\Groups(['node'])]
    #[Gedmo\Versioned]
    #[ApiProperty(
        description: 'Does this node act as a container for other nodes?',
        example: 'false',
    )]
    private bool $hideChildren = false;

    #[ORM\Column(name: 'children_order', type: 'string', length: 50)]
    #[SymfonySerializer\Groups(['node', 'node_listing'])]
    #[Assert\Length(max: 50)]
    #[Gedmo\Versioned]
    #[ApiProperty(
        description: 'This node children will be sorted by a given field',
        example: 'position',
        schema: [
            'type' => 'string',
            'enum' => ['position', 'nodeName', 'createdAt', 'updatedAt', 'publishedAt'],
            'example' => 'position',
        ],
    )]
    private string $childrenOrder = 'position';

    #[ORM\Column(name: 'children_order_direction', type: 'string', length: 4)]
    #[SymfonySerializer\Groups(['node', 'node_listing'])]
    #[Assert\Length(max: 4)]
    #[Assert\Choice(choices: ['ASC', 'DESC'])]
    #[Gedmo\Versioned]
    #[ApiProperty(
        description: 'This node children will be sorted ascendant or descendant',
        example: 'ASC',
        schema: [
            'type' => 'string',
            'enum' => ['ASC', 'DESC'],
            'example' => 'ASC',
        ],
    )]
    private string $childrenOrderDirection = 'ASC';

    #[ORM\Column(name: 'nodetype_name', type: 'string', length: 30)]
    #[SymfonySerializer\Ignore]
    private string $nodeTypeName;

    /**
     * @var Node|null
     */
    #[ORM\ManyToOne(targetEntity: Node::class, fetch: 'EAGER', inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_node_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[SymfonySerializer\Ignore]
    private ?LeafInterface $parent = null;

    /**
     * @var Collection<int, Node>
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Node::class, orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[SymfonySerializer\Groups(['node_children'])]
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
    #[ApiFilter(BaseFilter\SearchFilter::class, properties: [
        'nodesTags.tag' => 'exact',
        'nodesTags.tag.tagName' => 'exact',
    ])]
    #[ApiFilter(RoadizFilter\NotFilter::class, properties: [
        'nodesTags.tag.tagName',
    ])]
    // Use IntersectionFilter after SearchFilter!
    #[ApiFilter(RoadizFilter\IntersectionFilter::class, properties: [
        'nodesTags.tag',
        'nodesTags.tag.tagName',
    ])]
    private Collection $nodesTags;

    /**
     * @var Collection<int, NodesCustomForms>
     */
    #[ORM\OneToMany(mappedBy: 'node', targetEntity: NodesCustomForms::class, fetch: 'EXTRA_LAZY')]
    #[SymfonySerializer\Ignore]
    private Collection $customForms;

    /**
     * @var Collection<int, StackType>
     */
    #[ORM\OneToMany(mappedBy: 'node', targetEntity: StackType::class, cascade: ['persist'], orphanRemoval: true)]
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
    private Collection $bNodes;

    /**
     * @var Collection<int, NodesToNodes>
     */
    #[ORM\OneToMany(mappedBy: 'nodeB', targetEntity: NodesToNodes::class)]
    #[SymfonySerializer\Ignore]
    private Collection $aNodes;

    /**
     * @var Collection<int, AttributeValue>
     */
    #[ORM\OneToMany(mappedBy: 'node', targetEntity: AttributeValue::class, orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
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
        $this->initDateTimedTrait();
    }

    /**
     * @deprecated Use NodeStatus enum getLabel method
     */
    public static function getStatusLabel(int|string $status): string
    {
        $status = NodeStatus::tryFrom((int) $status) ?? throw new \InvalidArgumentException('Invalid status '.$status);

        return $status->getLabel();
    }

    /**
     * Dynamic node name will be updated against default
     * translated nodeSource title at each save.
     *
     * Disable this parameter if you need to protect your nodeName
     * from title changes.
     */
    public function isDynamicNodeName(): bool
    {
        return $this->dynamicNodeName;
    }

    /**
     * @return $this
     */
    public function setDynamicNodeName(bool $dynamicNodeName): static
    {
        $this->dynamicNodeName = (bool) $dynamicNodeName;

        return $this;
    }

    public function isHome(): bool
    {
        return $this->home;
    }

    /**
     * @return $this
     */
    public function setHome(bool $home): static
    {
        $this->home = $home;

        return $this;
    }

    public function isShadow(): bool
    {
        return $this->shadow;
    }

    public function setShadow(bool $shadow): static
    {
        $this->shadow = $shadow;

        if (true === $shadow) {
            // A shadow node requires a static name and must be locked
            $this->setDynamicNodeName(false);
            $this->setLocked(true);
        }

        return $this;
    }

    public function getStatus(): NodeStatus
    {
        return $this->status;
    }

    /**
     * @param int|string|NodeStatus $status Workflow only use <string> marking places
     *
     * @return $this
     *
     * @internal you should use node Workflow to perform change on status
     */
    public function setStatus(int|string|NodeStatus $status): static
    {
        if ($status instanceof NodeStatus) {
            $this->status = $status;
        } else {
            $this->status = NodeStatus::tryFrom((int) $status) ?? NodeStatus::DRAFT;
        }

        return $this;
    }

    public function setStatusAsString(string $name): static
    {
        $this->status = NodeStatus::fromName($name);

        return $this;
    }

    public function getStatusAsString(): string
    {
        return $this->status->name;
    }

    public function getTtl(): int
    {
        return $this->ttl ?? 0;
    }

    public function setTtl(?int $ttl): static
    {
        $this->ttl = $ttl;

        return $this;
    }

    #[\Override]
    public function isPublished(): bool
    {
        return $this->status->isPublished();
    }

    #[\Override]
    public function isPending(): bool
    {
        return $this->status->isPending();
    }

    #[\Override]
    public function isDraft(): bool
    {
        return $this->status->isDraft();
    }

    public function isDeleted(): bool
    {
        return $this->status->isDeleted();
    }

    public function isArchived(): bool
    {
        return $this->status->isArchived();
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    /**
     * @return $this
     */
    public function setLocked(bool $locked): static
    {
        $this->locked = $locked;

        return $this;
    }

    public function getHideChildren(): bool
    {
        return $this->hideChildren;
    }

    /**
     * @return $this
     */
    public function setHideChildren(bool $hideChildren): static
    {
        $this->hideChildren = $hideChildren;

        return $this;
    }

    public function isHidingChildren(): bool
    {
        return $this->hideChildren;
    }

    /**
     * @return $this
     */
    public function setHidingChildren(bool $hideChildren): static
    {
        $this->hideChildren = $hideChildren;

        return $this;
    }

    #[\Override]
    public function getChildrenOrder(): string
    {
        return $this->childrenOrder;
    }

    /**
     * @return $this
     */
    public function setChildrenOrder(string $childrenOrder): static
    {
        $this->childrenOrder = $childrenOrder;

        return $this;
    }

    #[\Override]
    public function getChildrenOrderDirection(): string
    {
        return $this->childrenOrderDirection;
    }

    /**
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
     *
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
    public function getTags(): Collection
    {
        return $this->nodesTags->map(fn (NodesTags $nodesTags) => $nodesTags->getTag());
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
     * @return $this
     */
    public function addTag(Tag $tag): static
    {
        if (
            !$this->getTags()->exists(fn ($key, Tag $existingTag) => $tag->getId() === $existingTag->getId())
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
        $nodeTags = $this->nodesTags->filter(fn (NodesTags $existingNodesTags) => $existingNodesTags->getTag()->getId() === $tag->getId());
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
     *
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
     * @return $this
     */
    public function removeStackType(NodeType $nodeType): static
    {
        $stackType = $this->stackTypes->findFirst(
            fn (int $key, StackType $stackType) => $stackType->getNodeTypeName() === $nodeType->getName()
        );

        if (null === $stackType) {
            return $this;
        }

        $this->stackTypes->removeElement($stackType);

        return $this;
    }

    /**
     * @return Collection<int, StackType>
     */
    public function getStackTypes(): Collection
    {
        return $this->stackTypes;
    }

    /**
     * @return $this
     */
    public function addStackType(NodeType $nodeType): static
    {
        if (!$this->getStackTypes()->exists(fn (int $key, StackType $stackType) => $stackType->getNodeTypeName() === $nodeType->getName())) {
            $this->getStackTypes()->add(
                new StackType(
                    $this,
                    $nodeType->getName()
                )
            );
        }

        return $this;
    }

    /**
     * Get node-sources using a given translation.
     *
     * @return Collection<int, NodesSources>
     */
    #[SymfonySerializer\Ignore]
    public function getNodeSourcesByTranslation(TranslationInterface $translation): Collection
    {
        return $this->nodeSources->filter(fn (NodesSources $nodeSource) => $nodeSource->getTranslation()->getLocale() === $translation->getLocale());
    }

    /**
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
     *
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
        return $this->getBNodes()->exists(fn ($key, NodesToNodes $element) => null !== $bNode->getNodeB()->getId()
            && $element->getNodeB()->getId() === $bNode->getNodeB()->getId()
            && $element->getFieldName() === $bNode->getFieldName());
    }

    /**
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

    public function clearBNodesForField(NodeTypeFieldInterface $field): static
    {
        $toRemoveCollection = $this->getBNodes()->filter(fn (NodesToNodes $element) => $element->getFieldName() === $field->getName());
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

    public function getNodeName(): string
    {
        return $this->nodeName;
    }

    /**
     * @return $this
     */
    public function setNodeName(string $nodeName): static
    {
        $this->nodeName = StringHandler::slugify($nodeName);

        return $this;
    }

    #[\Override]
    public function getNodeTypeName(): string
    {
        return $this->nodeTypeName;
    }

    public function setNodeTypeName(string $nodeType): static
    {
        $this->nodeTypeName = $nodeType;

        return $this;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * @return $this
     */
    public function setVisible(bool $visible): static
    {
        $this->visible = $visible;

        return $this;
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
            /** @var Collection<int, Node> $children */
            $children = $this->getChildren();
            $this->children = new ArrayCollection();
            foreach ($children as $child) {
                $cloneChild = clone $child;
                $this->addChild($cloneChild);
            }

            /** @var NodesTags[] $nodesTags */
            $nodesTags = $this->nodesTags->toArray();
            if (null !== $nodesTags) {
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
            if (false !== $nodeSource) {
                $namePrefix = '' != $nodeSource->getTitle() ?
                    $nodeSource->getTitle() :
                    $this->nodeName;
            } else {
                $namePrefix = $this->nodeName;
            }
            $this->setNodeName($namePrefix.'-'.uniqid());
            $this->setCreatedAt(new \DateTime());
            $this->setUpdatedAt(new \DateTime());
        }
    }

    #[\Override]
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

    #[\Override]
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
