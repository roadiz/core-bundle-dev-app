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
use Doctrine\Persistence\ObjectManager;
use Gedmo\Loggable\Loggable;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Api\Filter as RoadizFilter;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * NodesSources store Node content according to a translation and a NodeType.
 */
#[
    ORM\Entity(repositoryClass: NodesSourcesRepository::class),
    ORM\Table(name: 'nodes_sources'),
    ORM\Index(columns: ['discr']),
    ORM\Index(columns: ['title']),
    ORM\Index(columns: ['published_at']),
    ORM\Index(columns: ['no_index'], name: 'ns_no_index'),
    ORM\Index(columns: ['node_id', 'translation_id', 'published_at'], name: 'ns_node_translation_published'),
    ORM\Index(columns: ['node_id', 'discr', 'translation_id'], name: 'ns_node_discr_translation'),
    ORM\Index(columns: ['node_id', 'discr', 'translation_id', 'published_at'], name: 'ns_node_discr_translation_published'),
    ORM\Index(columns: ['translation_id', 'published_at'], name: 'ns_translation_published'),
    ORM\Index(columns: ['discr', 'translation_id'], name: 'ns_discr_translation'),
    ORM\Index(columns: ['discr', 'translation_id', 'published_at'], name: 'ns_discr_translation_published'),
    ORM\Index(columns: ['title', 'published_at'], name: 'ns_title_published'),
    ORM\Index(columns: ['title', 'translation_id', 'published_at'], name: 'ns_title_translation_published'),
    ORM\UniqueConstraint(columns: ['node_id', 'translation_id']),
    ORM\InheritanceType('JOINED'),
    // Limit discriminator column to 30 characters for indexing optimization
    ORM\DiscriminatorColumn(name: 'discr', type: 'string', length: 30),
    ORM\HasLifecycleCallbacks,
    Gedmo\Loggable(logEntryClass: UserLogEntry::class),
    UniqueEntity(fields: ['node', 'translation']),
    ApiFilter(PropertyFilter::class),
    ApiFilter(RoadizFilter\LocaleFilter::class)
]
class NodesSources extends AbstractEntity implements Loggable
{
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    protected ?ObjectManager $objectManager = null;

    /**
     * @var Collection<int, Redirection>
     */
    #[ORM\OneToMany(mappedBy: 'redirectNodeSource', targetEntity: Redirection::class)]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    protected Collection $redirections;

    #[ApiFilter(BaseFilter\SearchFilter::class, strategy: 'partial')]
    #[ORM\Column(name: 'title', type: 'string', length: 250, unique: false, nullable: true)]
    #[SymfonySerializer\Groups(['nodes_sources', 'nodes_sources_base', 'log_sources'])]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_base', 'log_sources'])]
    #[Gedmo\Versioned]
    #[Assert\Length(max: 250)]
    #[ApiProperty(
        description: 'Content title',
        example: 'This is a title',
    )]
    protected ?string $title = null;

    #[ApiFilter(BaseFilter\DateFilter::class)]
    #[ApiFilter(BaseFilter\OrderFilter::class)]
    #[ApiFilter(RoadizFilter\ArchiveFilter::class)]
    #[ORM\Column(name: 'published_at', type: 'datetime', unique: false, nullable: true)]
    #[SymfonySerializer\Groups(['nodes_sources', 'nodes_sources_base'])]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_base'])]
    #[Gedmo\Versioned]
    #[ApiProperty(
        description: 'Content publication date and time',
    )]
    protected ?\DateTime $publishedAt = null;

    #[ApiFilter(BaseFilter\SearchFilter::class, strategy: 'partial')]
    #[ORM\Column(name: 'meta_title', type: 'string', length: 150, unique: false)]
    #[SymfonySerializer\Groups(['nodes_sources'])]
    #[Serializer\Groups(['nodes_sources'])]
    #[Gedmo\Versioned]
    #[Assert\Length(max: 150)]
    #[ApiProperty(
        description: 'Title for search engine optimization, used in HTML title tag',
        example: 'This is a title',
    )]
    protected string $metaTitle = '';

    #[ApiFilter(BaseFilter\SearchFilter::class, strategy: 'partial')]
    #[ORM\Column(name: 'meta_description', type: 'text')]
    #[SymfonySerializer\Groups(['nodes_sources'])]
    #[Serializer\Groups(['nodes_sources'])]
    #[Gedmo\Versioned]
    #[ApiProperty(
        description: 'Description for search engine optimization, used in HTML meta description tag',
        example: 'This is a description',
    )]
    protected string $metaDescription = '';

    #[ApiFilter(BaseFilter\BooleanFilter::class)]
    #[ORM\Column(name: 'no_index', type: 'boolean', options: ['default' => false])]
    #[SymfonySerializer\Groups(['nodes_sources'])]
    #[Serializer\Groups(['nodes_sources'])]
    #[Gedmo\Versioned]
    #[ApiProperty(
        description: 'Do not allow robots to index this content, used in HTML meta robots tag',
        example: 'false',
    )]
    protected bool $noIndex = false;

    #[ApiFilter(BaseFilter\SearchFilter::class, properties: [
        'node.id' => 'exact',
        'node.nodeName' => 'exact',
        'node.parent' => 'exact',
        'node.parent.nodeName' => 'exact',
        'node.nodesTags.tag' => 'exact',
        'node.nodesTags.tag.tagName' => 'exact',
        'node.nodeType' => 'exact',
        'node.nodeType.name' => 'exact',
    ])]
    #[ApiFilter(BaseFilter\OrderFilter::class, properties: [
        'node.position',
        'node.createdAt',
        'node.updatedAt',
    ])]
    #[ApiFilter(BaseFilter\NumericFilter::class, properties: [
        'node.position',
    ])]
    #[ApiFilter(BaseFilter\RangeFilter::class, properties: [
        'node.position',
    ])]
    #[ApiFilter(BaseFilter\DateFilter::class, properties: [
        'node.createdAt',
        'node.updatedAt',
    ])]
    #[ApiFilter(BaseFilter\BooleanFilter::class, properties: [
        'node.visible',
        'node.home',
        'node.nodeType.reachable',
        'node.nodeType.publishable',
    ])]
    #[ApiFilter(RoadizFilter\NotFilter::class, properties: [
        'node.nodeType.name',
        'node.id',
        'node.nodesTags.tag.tagName',
    ])]
    // Use IntersectionFilter after SearchFilter!
    #[ApiFilter(RoadizFilter\IntersectionFilter::class, properties: [
        'node.nodesTags.tag',
        'node.nodesTags.tag.tagName',
    ])]
    #[ORM\ManyToOne(targetEntity: Node::class, cascade: ['persist'], fetch: 'EAGER', inversedBy: 'nodeSources')]
    #[ORM\JoinColumn(name: 'node_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[SymfonySerializer\Groups(['nodes_sources', 'nodes_sources_base', 'log_sources'])]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_base', 'log_sources'])]
    #[Assert\Valid]
    #[Assert\NotNull]
    private Node $node;

    #[ApiFilter(BaseFilter\SearchFilter::class, properties: [
        'translation.id' => 'exact',
        'translation.locale' => 'exact',
    ])]
    #[ORM\ManyToOne(targetEntity: Translation::class, inversedBy: 'nodeSources')]
    #[ORM\JoinColumn(name: 'translation_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[SymfonySerializer\Groups(['translation_base'])]
    #[Serializer\Groups(['translation_base'])]
    #[Assert\NotNull]
    private TranslationInterface $translation;

    /**
     * @var Collection<int, UrlAlias>
     */
    #[ORM\OneToMany(
        mappedBy: 'nodeSource',
        targetEntity: UrlAlias::class,
        cascade: ['all']
    )]
    #[SymfonySerializer\Ignore]
    private Collection $urlAliases;

    /**
     * @var Collection<int, NodesSourcesDocuments>
     */
    #[ORM\OneToMany(
        mappedBy: 'nodeSource',
        targetEntity: NodesSourcesDocuments::class,
        cascade: ['persist'],
        fetch: 'LAZY',
        orphanRemoval: true
    )]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    private Collection $documentsByFields;

    /**
     * Create a new NodeSource with its Node and Translation.
     */
    public function __construct(Node $node, TranslationInterface $translation)
    {
        $this->setNode($node);
        $this->translation = $translation;
        $this->urlAliases = new ArrayCollection();
        $this->documentsByFields = new ArrayCollection();
        $this->redirections = new ArrayCollection();
    }

    #[Serializer\Exclude]
    public function injectObjectManager(ObjectManager $objectManager): void
    {
        $this->objectManager = $objectManager;
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->getNode()->setUpdatedAt(new \DateTime('now'));
    }

    public function getNode(): Node
    {
        return $this->node;
    }

    public function setNode(Node $node): NodesSources
    {
        $this->node = $node;
        $node->addNodeSources($this);

        return $this;
    }

    /**
     * @return $this
     */
    public function addUrlAlias(UrlAlias $urlAlias): NodesSources
    {
        if (!$this->urlAliases->contains($urlAlias)) {
            $this->urlAliases->add($urlAlias);
            $urlAlias->setNodeSource($this);
        }

        return $this;
    }

    public function clearDocumentsByFields(NodeTypeFieldInterface $field): NodesSources
    {
        $toRemoveCollection = $this->getDocumentsByFields()->filter(
            function (NodesSourcesDocuments $element) use ($field) {
                return $element->getFieldName() === $field->getName();
            }
        );
        /** @var NodesSourcesDocuments $toRemove */
        foreach ($toRemoveCollection as $toRemove) {
            $this->getDocumentsByFields()->removeElement($toRemove);
        }

        return $this;
    }

    /**
     * @return Collection<int, NodesSourcesDocuments>
     */
    public function getDocumentsByFields(): Collection
    {
        return $this->documentsByFields;
    }

    /**
     * Get at least one document to represent this node-source as image.
     */
    #[SymfonySerializer\Ignore]
    public function getOneDisplayableDocument(): ?DocumentInterface
    {
        return $this->getDocumentsByFields()->filter(function (NodesSourcesDocuments $nsd) {
            return null !== $nsd->getDocument()
                && !$nsd->getDocument()->isPrivate()
                && ($nsd->getDocument()->isImage() || $nsd->getDocument()->isSvg())
                && $nsd->getDocument()->isProcessable();
        })->map(function (NodesSourcesDocuments $nsd) {
            return $nsd->getDocument();
        })->first() ?: null;
    }

    /**
     * @param Collection<int, NodesSourcesDocuments> $documentsByFields
     */
    public function setDocumentsByFields(Collection $documentsByFields): NodesSources
    {
        $this->documentsByFields->clear();
        foreach ($documentsByFields as $documentsByField) {
            if (!$this->hasNodesSourcesDocuments($documentsByField)) {
                $this->addDocumentsByFields($documentsByField);
            }
        }

        return $this;
    }

    #[SymfonySerializer\Ignore]
    public function hasNodesSourcesDocuments(NodesSourcesDocuments $nodesSourcesDocuments): bool
    {
        return $this->getDocumentsByFields()->exists(
            function ($key, NodesSourcesDocuments $element) use ($nodesSourcesDocuments) {
                return null !== $nodesSourcesDocuments->getDocument()->getId()
                    && $element->getDocument()->getId() === $nodesSourcesDocuments->getDocument()->getId()
                    && $element->getFieldName() === $nodesSourcesDocuments->getFieldName();
            }
        );
    }

    /**
     * Used by any NSClass to add directly new documents to source.
     *
     * @return $this
     */
    public function addDocumentsByFields(NodesSourcesDocuments $nodesSourcesDocuments): NodesSources
    {
        if (!$this->getDocumentsByFields()->contains($nodesSourcesDocuments)) {
            $this->getDocumentsByFields()->add($nodesSourcesDocuments);
            $nodesSourcesDocuments->setNodeSource($this);
        }

        return $this;
    }

    /**
     * @param NodeTypeField $field
     *
     * @return Document[]
     */
    public function getDocumentsByFieldsWithField(NodeTypeFieldInterface $field): array
    {
        $criteria = Criteria::create();
        $criteria->orderBy(['position' => 'ASC']);

        return $this->getDocumentsByFields()
            ->matching($criteria)
            ->filter(function (NodesSourcesDocuments $element) use ($field) {
                return $element->getFieldName() === $field->getName();
            })
            ->map(function (NodesSourcesDocuments $nodesSourcesDocuments) {
                return $nodesSourcesDocuments->getDocument();
            })
            ->toArray()
        ;
    }

    /**
     * @return Document[]
     */
    public function getDocumentsByFieldsWithName(string $fieldName): array
    {
        $criteria = Criteria::create();
        $criteria->orderBy(['position' => 'ASC']);

        return $this->getDocumentsByFields()
            ->matching($criteria)
            ->filter(function (NodesSourcesDocuments $element) use ($fieldName) {
                return $element->getFieldName() === $fieldName;
            })
            ->map(function (NodesSourcesDocuments $nodesSourcesDocuments) {
                return $nodesSourcesDocuments->getDocument();
            })
            ->toArray()
        ;
    }

    /**
     * @return Collection<int, Redirection>
     */
    public function getRedirections(): Collection
    {
        return $this->redirections;
    }

    /**
     * @param Collection<int, Redirection> $redirections
     */
    public function setRedirections(Collection $redirections): NodesSources
    {
        $this->redirections = $redirections;

        return $this;
    }

    public function getPublishedAt(): ?\DateTime
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTime $publishedAt = null): NodesSources
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getMetaTitle(): string
    {
        return $this->metaTitle;
    }

    /**
     * @return $this
     */
    public function setMetaTitle(?string $metaTitle): NodesSources
    {
        $this->metaTitle = null !== $metaTitle ? trim($metaTitle) : '';

        return $this;
    }

    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    /**
     * @return $this
     */
    public function setMetaDescription(?string $metaDescription): NodesSources
    {
        $this->metaDescription = null !== $metaDescription ? trim($metaDescription) : '';

        return $this;
    }

    public function isNoIndex(): bool
    {
        return $this->noIndex;
    }

    public function setNoIndex(bool $noIndex): NodesSources
    {
        $this->noIndex = $noIndex;

        return $this;
    }

    #[SymfonySerializer\SerializedName('slug')]
    #[SymfonySerializer\Groups(['nodes_sources', 'nodes_sources_base'])]
    #[Serializer\SerializedName('slug')]
    #[Serializer\VirtualProperty]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_base'])]
    public function getIdentifier(): string
    {
        $urlAlias = $this->getUrlAliases()->first();
        if (false !== $urlAlias && '' !== $urlAlias->getAlias()) {
            return $urlAlias->getAlias();
        }

        return $this->getNode()->getNodeName();
    }

    /**
     * @return Collection<int, UrlAlias>
     */
    public function getUrlAliases(): Collection
    {
        return $this->urlAliases;
    }

    /**
     * Get parent node source based on the same translation.
     */
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    public function getParent(): ?NodesSources
    {
        /** @var Node|null $parent */
        $parent = $this->getNode()->getParent();
        if (null !== $parent) {
            /** @var NodesSources|false $nodeSources */
            $nodeSources = $parent->getNodeSourcesByTranslation($this->translation)->first();

            return $nodeSources ?: null;
        } else {
            return null;
        }
    }

    public function __toString(): string
    {
        return (string) $this->getId();
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @return $this
     */
    public function setTitle(?string $title): NodesSources
    {
        $this->title = null !== $title ? trim($title) : null;

        return $this;
    }

    public function getTranslation(): TranslationInterface
    {
        return $this->translation;
    }

    /**
     * @return $this
     */
    public function setTranslation(TranslationInterface $translation): NodesSources
    {
        $this->translation = $translation;

        return $this;
    }

    #[SymfonySerializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[SymfonySerializer\SerializedName('@type')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[Serializer\SerializedName('@type')]
    #[Serializer\VirtualProperty]
    public function getNodeTypeName(): string
    {
        return 'NodesSources';
    }

    /**
     * Overridden in NS classes.
     */
    public function isPublishable(): bool
    {
        return $this->getNode()->getNodeType()->isPublishable();
    }

    /**
     * Overridden in NS classes.
     */
    public function isReachable(): bool
    {
        return $this->getNode()->getNodeType()->isReachable();
    }

    /**
     * Set base data from another node-source.
     *
     * @return $this
     */
    public function withNodesSources(NodesSources $nodesSources): self
    {
        $this->setTitle($nodesSources->getTitle());
        $this->setPublishedAt($nodesSources->getPublishedAt());
        $this->setMetaTitle($nodesSources->getMetaTitle());
        $this->setMetaDescription($nodesSources->getMetaDescription());
        $this->setNoIndex($nodesSources->isNoIndex());

        return $this;
    }

    /**
     * Returns current listing sort options OR parent node's if parent node is hiding children.
     */
    #[Serializer\Groups(['node_listing'])]
    #[SymfonySerializer\Groups(['node_listing'])]
    public function getListingSortOptions(): array
    {
        if (null !== $this->getParent() && $this->getParent()->getNode()->isHidingChildren()) {
            return $this->getParent()->getListingSortOptions();
        }

        return match ($this->getNode()->getChildrenOrder()) {
            'position' => [
                'node.position' => $this->getNode()->getChildrenOrderDirection(),
            ],
            'nodeName' => [
                'node.nodeName' => $this->getNode()->getChildrenOrderDirection(),
            ],
            'createdAt' => [
                'node.createdAt' => $this->getNode()->getChildrenOrderDirection(),
            ],
            'updatedAt' => [
                'node.updatedAt' => $this->getNode()->getChildrenOrderDirection(),
            ],
            default => [
                'publishedAt' => $this->getNode()->getChildrenOrderDirection(),
            ],
        };
    }

    /**
     * After clone method.
     *
     * Be careful not to persist nor flush current entity after
     * calling clone as it empties its relations.
     */
    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $documentsByFields = $this->getDocumentsByFields();
            $this->documentsByFields = new ArrayCollection();
            foreach ($documentsByFields as $documentsByField) {
                $cloneDocumentsByField = clone $documentsByField;
                $this->documentsByFields->add($cloneDocumentsByField);
                $cloneDocumentsByField->setNodeSource($this);
            }
            // Clear url-aliases before cloning.
            $this->urlAliases->clear();
        }
    }
}
