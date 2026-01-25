<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use ApiPlatform\Doctrine\Orm\Filter as BaseFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\DateTimedInterface;
use RZ\Roadiz\Core\AbstractEntities\DateTimedTrait;
use RZ\Roadiz\Core\AbstractEntities\LeafInterface;
use RZ\Roadiz\Core\AbstractEntities\LeafTrait;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\SequentialIdTrait;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Api\Filter\NodesTagsFilter;
use RZ\Roadiz\CoreBundle\Api\Filter\NotFilter;
use RZ\Roadiz\CoreBundle\Repository\TagRepository;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Tags are hierarchical entities used to qualify Nodes.
 *
 * @implements LeafInterface<Tag>
 */
#[ORM\Entity(repositoryClass: TagRepository::class),
    ORM\HasLifecycleCallbacks,
    ORM\Table(name: 'tags'),
    ORM\Index(columns: ['visible']),
    ORM\Index(columns: ['locked']),
    ORM\Index(columns: ['position']),
    ORM\Index(columns: ['created_at']),
    ORM\Index(columns: ['updated_at']),
    ORM\Index(columns: ['parent_tag_id', 'position'], name: 'tag_parent_position'),
    ORM\Index(columns: ['visible', 'position'], name: 'tag_visible_position'),
    ORM\Index(columns: ['parent_tag_id', 'visible'], name: 'tag_parent_visible'),
    ORM\Index(columns: ['parent_tag_id', 'visible', 'position'], name: 'tag_parent_visible_position'),
    UniqueEntity(fields: ['tagName']),
    ApiFilter(PropertyFilter::class),
    ApiFilter(NodesTagsFilter::class),
    ApiFilter(BaseFilter\OrderFilter::class, properties: [
        'tagName',
        'position',
        'createdAt',
        'updatedAt',
    ])]
class Tag implements DateTimedInterface, LeafInterface, PersistableInterface, \Stringable
{
    use SequentialIdTrait;
    use DateTimedTrait;
    use LeafTrait;

    #[ORM\Column(
        name: 'color',
        type: 'string',
        length: 7,
        unique: false,
        nullable: false,
        options: ['default' => '#000000']
    )]
    #[SymfonySerializer\Groups(['tag', 'tag_color', 'color'])]
    #[Assert\Length(max: 7)]
    #[ApiProperty(
        description: 'Tag color in hexadecimal format.',
        example: '#ff0000',
    )]
    protected string $color = '#000000';

    /**
     * @var Tag|null
     */
    #[ApiFilter(BaseFilter\SearchFilter::class, properties: [
        'parent.id' => 'exact',
        'parent.tagName' => 'exact',
    ])]
    #[ApiFilter(NotFilter::class, properties: [
        'parent.id',
        'parent.tagName',
    ])]
    #[ORM\ManyToOne(targetEntity: Tag::class, fetch: 'EXTRA_LAZY', inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_tag_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[SymfonySerializer\MaxDepth(2)]
    #[SymfonySerializer\Groups(['tag_parent'])]
    protected ?LeafInterface $parent = null;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\OneToMany(
        mappedBy: 'parent',
        targetEntity: Tag::class,
        cascade: ['persist', 'merge'],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[SymfonySerializer\Groups(['tag_children'])]
    protected Collection $children;

    /**
     * @var Collection<int, TagTranslation>
     */
    #[ORM\OneToMany(
        mappedBy: 'tag',
        targetEntity: TagTranslation::class,
        cascade: ['all'],
        orphanRemoval: true
    )]
    #[SymfonySerializer\Groups(['translated_tag'])]
    protected Collection $translatedTags;

    #[ApiFilter(BaseFilter\SearchFilter::class, strategy: 'partial')]
    #[ORM\Column(name: 'tag_name', type: 'string', length: 250, unique: true)]
    #[SymfonySerializer\Ignore]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    #[ApiProperty(
        description: 'Unique tag name (slug) used to build content URL or filter queries.',
        example: 'this-is-a-tag-name',
    )]
    private string $tagName = '';

    #[SymfonySerializer\Ignore]
    private string $dirtyTagName = '';

    #[ApiFilter(BaseFilter\BooleanFilter::class)]
    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    #[SymfonySerializer\Groups(['tag', 'tag_base', 'node', 'nodes_sources'])]
    #[ApiProperty(
        description: 'Is this tag visible in website?',
        example: 'true',
    )]
    private bool $visible = true;

    #[ORM\Column(name: 'children_order', type: 'string', length: 60, options: ['default' => 'position'])]
    #[SymfonySerializer\Ignore]
    #[Assert\Length(max: 60)]
    #[ApiProperty(
        description: 'This tag children will be sorted by a given field',
        example: 'position',
        schema: [
            'type' => 'string',
            'enum' => ['position', 'tagName', 'createdAt', 'updatedAt', 'publishedAt'],
            'example' => 'position',
        ],
    )]
    private string $childrenOrder = 'position';

    #[ORM\Column(name: 'children_order_direction', type: 'string', length: 4, options: ['default' => 'ASC'])]
    #[SymfonySerializer\Ignore]
    #[Assert\Length(max: 4)]
    #[ApiProperty(
        description: 'This tag children will be sorted ascendant or descendant',
        example: 'ASC',
        schema: [
            'type' => 'string',
            'enum' => ['ASC', 'DESC'],
            'example' => 'ASC',
        ],
    )]
    private string $childrenOrderDirection = 'ASC';

    #[ApiFilter(BaseFilter\BooleanFilter::class)]
    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    #[SymfonySerializer\Ignore]
    #[ApiProperty(
        description: 'Is this tag locked to prevent deletion and renaming?',
        example: 'false',
    )]
    private bool $locked = false;

    /**
     * @var Collection<int, NodesTags>
     */
    #[ORM\OneToMany(
        mappedBy: 'tag',
        targetEntity: NodesTags::class,
        cascade: ['persist'],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[SymfonySerializer\Ignore]
    #[ApiFilter(BaseFilter\SearchFilter::class, properties: [
        'nodesTags.node' => 'exact',
        'nodesTags.node.nodeName' => 'exact',
        'nodesTags.node.parent' => 'exact',
        'nodesTags.node.parent.nodeName' => 'exact',
        'nodesTags.node.nodeTypeName' => 'exact',
        'nodesTags.node.nodesTags.tag' => 'exact',
        'nodesTags.node.nodesTags.tag.tagName' => 'exact',
    ])]
    #[ApiFilter(BaseFilter\BooleanFilter::class, properties: [
        'nodesTags.node.visible',
    ])]
    private Collection $nodesTags;

    public function __construct()
    {
        $this->nodesTags = new ArrayCollection();
        $this->translatedTags = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->initDateTimedTrait();
    }

    /**
     * Gets the value of dirtyTagName.
     */
    public function getDirtyTagName(): string
    {
        return $this->dirtyTagName;
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

    /**
     * @return Collection<int, Node>
     */
    public function getNodes(): Collection
    {
        return $this->nodesTags->map(fn (NodesTags $nodesTags) => $nodesTags->getNode());
    }

    /**
     * Get tag full path using tag names.
     */
    public function getFullPath(): string
    {
        $parents = $this->getParents();
        $path = [];

        foreach ($parents as $parent) {
            if ($parent instanceof Tag) {
                $path[] = $parent->getTagName();
            }
        }

        $path[] = $this->getTagName();

        return implode('/', $path);
    }

    public function getTagName(): string
    {
        return $this->tagName;
    }

    /**
     * @return $this
     */
    public function setTagName(string $tagName): static
    {
        $this->dirtyTagName = $tagName;
        $this->tagName = StringHandler::slugify($tagName);

        return $this;
    }

    /**
     * @return Collection<int, TagTranslation>
     */
    #[SymfonySerializer\Ignore]
    public function getTranslatedTagsByTranslation(TranslationInterface $translation): Collection
    {
        return $this->translatedTags->filter(fn (TagTranslation $tagTranslation) => $tagTranslation->getTranslation()->getLocale() === $translation->getLocale());
    }

    #[SymfonySerializer\Ignore]
    public function getTranslatedTagsByDefaultTranslation(): ?TagTranslation
    {
        return $this->translatedTags->findFirst(fn (int $key, TagTranslation $tagTranslation) => $tagTranslation->getTranslation()->isDefaultTranslation());
    }

    public function getOneLineSummary(): string
    {
        return $this->getId().' — '.$this->getTagName().
            ' — Visible : '.($this->isVisible() ? 'true' : 'false').PHP_EOL;
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
     * Gets the value of color.
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * Sets the value of color.
     *
     * @param string|null $color the color
     */
    public function setColor(?string $color): static
    {
        $this->color = $color ?? '';

        return $this;
    }

    /**
     * Gets the value of childrenOrder.
     */
    public function getChildrenOrder(): string
    {
        return $this->childrenOrder;
    }

    /**
     * Sets the value of childrenOrder.
     *
     * @param string $childrenOrder the children order
     */
    public function setChildrenOrder(string $childrenOrder): static
    {
        $this->childrenOrder = $childrenOrder;

        return $this;
    }

    /**
     * Gets the value of childrenOrderDirection.
     */
    public function getChildrenOrderDirection(): string
    {
        return $this->childrenOrderDirection;
    }

    /**
     * Sets the value of childrenOrderDirection.
     *
     * @param string $childrenOrderDirection the children order direction
     */
    public function setChildrenOrderDirection(string $childrenOrderDirection): static
    {
        $this->childrenOrderDirection = $childrenOrderDirection;

        return $this;
    }

    #[\Override]
    public function __toString(): string
    {
        return (string) $this->getId();
    }

    #[SymfonySerializer\Ignore]
    public function getName(): ?string
    {
        return $this->getTranslatedTags()->first() ?
            $this->getTranslatedTags()->first()->getName() :
            $this->getTagName();
    }

    /**
     * @return Collection<int, TagTranslation>
     */
    public function getTranslatedTags(): Collection
    {
        return $this->translatedTags;
    }

    /**
     * @param Collection<int, TagTranslation> $translatedTags
     *
     * @return $this
     */
    public function setTranslatedTags(Collection $translatedTags): static
    {
        $this->translatedTags = $translatedTags;
        /** @var TagTranslation $translatedTag */
        foreach ($this->translatedTags as $translatedTag) {
            $translatedTag->setTag($this);
        }

        return $this;
    }

    #[SymfonySerializer\Ignore]
    public function getDescription(): ?string
    {
        return $this->getTranslatedTags()->first() ?
            $this->getTranslatedTags()->first()->getDescription() :
            '';
    }

    #[SymfonySerializer\Ignore]
    public function getDocuments(): array
    {
        return $this->getTranslatedTags()->first() ?
            $this->getTranslatedTags()->first()->getDocuments() :
            [];
    }

    #[\Override]
    public function setParent(?LeafInterface $parent = null): static
    {
        if ($parent === $this) {
            throw new \InvalidArgumentException('An entity cannot have itself as a parent.');
        }
        if (null !== $parent && !$parent instanceof Tag) {
            throw new \InvalidArgumentException('A tag can only have a Tag entity as a parent');
        }
        $this->parent = $parent;
        $this->parent?->addChild($this);

        return $this;
    }

    #[ApiProperty(
        description: 'Unique tag name (slug) used to build content URL or filter queries.',
        example: 'this-is-a-tag-name',
    )]
    #[SymfonySerializer\SerializedName('slug')]
    #[SymfonySerializer\Groups(['tag', 'tag_base', 'node', 'nodes_sources'])]
    public function getSlug(): string
    {
        return $this->getTagName();
    }
}
