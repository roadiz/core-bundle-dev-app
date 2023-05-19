<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter as BaseFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use ApiPlatform\Metadata\ApiFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\ObjectManager;
use Gedmo\Loggable\Loggable;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use RuntimeException;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Api\Filter as RoadizFilter;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * NodesSources store Node content according to a translation and a NodeType.
 */
#[
    ORM\Entity(repositoryClass: NodesSourcesRepository::class),
    ORM\Table(name: "nodes_sources"),
    ORM\Index(columns: ["discr"]),
    ORM\Index(columns: ["title"]),
    ORM\Index(columns: ["published_at"]),
    ORM\Index(columns: ["no_index"]),
    ORM\Index(columns: ["node_id", "translation_id", "published_at"], name: "ns_node_translation_published"),
    ORM\Index(columns: ["node_id", "discr", "translation_id"], name: "ns_node_discr_translation"),
    ORM\Index(columns: ["node_id", "discr", "translation_id", "published_at"], name: "ns_node_discr_translation_published"),
    ORM\Index(columns: ["translation_id", "published_at"], name: "ns_translation_published"),
    ORM\Index(columns: ["discr", "translation_id"], name: "ns_discr_translation"),
    ORM\Index(columns: ["discr", "translation_id", "published_at"], name: "ns_discr_translation_published"),
    ORM\Index(columns: ["title", "published_at"], name: "ns_title_published"),
    ORM\Index(columns: ["title", "translation_id", "published_at"], name: "ns_title_translation_published"),
    ORM\UniqueConstraint(columns: ["node_id", "translation_id"]),
    ORM\InheritanceType("JOINED"),
    // Limit discriminator column to 30 characters for indexing optimization
    ORM\DiscriminatorColumn(name: "discr", type: "string", length: 30),
    ORM\HasLifecycleCallbacks,
    Gedmo\Loggable(logEntryClass: UserLogEntry::class),
    UniqueEntity(fields: ["node", "translation"]),
    ApiFilter(PropertyFilter::class),
    ApiFilter(RoadizFilter\LocaleFilter::class)
]
class NodesSources extends AbstractEntity implements Loggable
{
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    protected ?ObjectManager $objectManager = null;

    /**
     * @var Collection<int, Log>
     */
    #[ORM\OneToMany(mappedBy: 'nodeSource', targetEntity: Log::class)]
    #[ORM\OrderBy(['datetime' => 'DESC'])]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    protected Collection $logs;

    /**
     * @var Collection<int, Redirection>
     */
    #[ORM\OneToMany(mappedBy: 'redirectNodeSource', targetEntity: Redirection::class)]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    protected Collection $redirections;

    #[ApiFilter(BaseFilter\SearchFilter::class, strategy: "partial")]
    #[ORM\Column(name: 'title', type: 'string', unique: false, nullable: true)]
    #[SymfonySerializer\Groups(['nodes_sources', 'nodes_sources_base', 'log_sources'])]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_base', 'log_sources'])]
    #[Gedmo\Versioned]
    protected ?string $title = null;

    #[ApiFilter(BaseFilter\DateFilter::class)]
    #[ApiFilter(BaseFilter\OrderFilter::class)]
    #[ApiFilter(RoadizFilter\ArchiveFilter::class)]
    #[ORM\Column(name: 'published_at', type: 'datetime', unique: false, nullable: true)]
    #[SymfonySerializer\Groups(['nodes_sources', 'nodes_sources_base'])]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_base'])]
    #[Gedmo\Versioned]
    protected ?\DateTime $publishedAt = null;

    #[ApiFilter(BaseFilter\SearchFilter::class, strategy: "partial")]
    #[ORM\Column(name: 'meta_title', type: 'string', unique: false)]
    #[SymfonySerializer\Groups(['nodes_sources'])]
    #[Serializer\Groups(['nodes_sources'])]
    #[Gedmo\Versioned]
    protected string $metaTitle = '';

    #[ORM\Column(name: 'meta_keywords', type: 'text')]
    #[SymfonySerializer\Groups(['nodes_sources'])]
    #[Serializer\Groups(['nodes_sources'])]
    #[Gedmo\Versioned]
    protected string $metaKeywords = '';

    #[ApiFilter(BaseFilter\SearchFilter::class, strategy: "partial")]
    #[ORM\Column(name: 'meta_description', type: 'text')]
    #[SymfonySerializer\Groups(['nodes_sources'])]
    #[Serializer\Groups(['nodes_sources'])]
    #[Gedmo\Versioned]
    protected string $metaDescription = '';

    #[ApiFilter(BaseFilter\BooleanFilter::class)]
    #[ORM\Column(name: 'no_index', type: 'boolean', options: ['default' => false])]
    #[SymfonySerializer\Groups(['nodes_sources'])]
    #[Serializer\Groups(['nodes_sources'])]
    #[Gedmo\Versioned]
    protected bool $noIndex = false;

    #[ApiFilter(BaseFilter\SearchFilter::class, properties: [
        "node.id" => "exact",
        "node.nodeName" => "exact",
        "node.parent" => "exact",
        "node.parent.nodeName" => "exact",
        "node.nodesTags.tag" => "exact",
        "node.nodesTags.tag.tagName" => "exact",
        "node.nodeType" => "exact",
        "node.nodeType.name" => "exact"
    ])]
    #[ApiFilter(BaseFilter\OrderFilter::class, properties: [
        "node.position",
        "node.createdAt",
        "node.updatedAt"
    ])]
    #[ApiFilter(BaseFilter\DateFilter::class, properties: [
        "node.createdAt",
        "node.updatedAt"
    ])]
    #[ApiFilter(BaseFilter\BooleanFilter::class, properties: [
        "node.visible",
        "node.home",
        "node.nodeType.reachable",
        "node.nodeType.publishable"
    ])]
    #[ApiFilter(RoadizFilter\NotFilter::class, properties: [
        "node.nodeType.name",
        "node.id",
        "node.nodesTags.tag.tagName",
    ])]
    # Use IntersectionFilter after SearchFilter!
    #[ApiFilter(RoadizFilter\IntersectionFilter::class, properties: [
        "node.nodesTags.tag",
        "node.nodesTags.tag.tagName",
    ])]
    #[ORM\ManyToOne(targetEntity: Node::class, cascade: ['persist'], fetch: 'EAGER', inversedBy: 'nodeSources')]
    #[ORM\JoinColumn(name: 'node_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[SymfonySerializer\Groups(['nodes_sources', 'nodes_sources_base', 'log_sources'])]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_base', 'log_sources'])]
    private ?Node $node = null;

    #[ApiFilter(BaseFilter\SearchFilter::class, properties: [
        "translation.id" => "exact",
        "translation.locale" => "exact",
    ])]
    #[ORM\ManyToOne(targetEntity: Translation::class, inversedBy: 'nodeSources')]
    #[ORM\JoinColumn(name: 'translation_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[SymfonySerializer\Groups(['translation_base'])]
    #[Serializer\Groups(['translation_base'])]
    private ?TranslationInterface $translation = null;

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
     *
     * @param Node $node
     * @param TranslationInterface $translation
     */
    public function __construct(Node $node, TranslationInterface $translation)
    {
        $this->setNode($node);
        $this->translation = $translation;
        $this->urlAliases = new ArrayCollection();
        $this->documentsByFields = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->redirections = new ArrayCollection();
    }

    /**
     * @inheritDoc
     * @Serializer\Exclude
     */
    public function injectObjectManager(ObjectManager $objectManager): void
    {
        $this->objectManager = $objectManager;
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->getNode()?->setUpdatedAt(new \DateTime("now"));
    }

    /**
     * @return Node|null
     */
    public function getNode(): ?Node
    {
        return $this->node;
    }

    /**
     * @param Node|null $node
     *
     * @return $this
     */
    public function setNode(Node $node = null): NodesSources
    {
        $this->node = $node;
        if (null !== $node) {
            $node->addNodeSources($this);
        }

        return $this;
    }

    /**
     * @param UrlAlias $urlAlias
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

    public function clearDocumentsByFields(NodeTypeField $nodeTypeField): NodesSources
    {
        $toRemoveCollection = $this->getDocumentsByFields()->filter(
            function (NodesSourcesDocuments $element) use ($nodeTypeField) {
                return $element->getField()->getId() === $nodeTypeField->getId();
            }
        );
        /** @var NodesSourcesDocuments $toRemove */
        foreach ($toRemoveCollection as $toRemove) {
            $this->getDocumentsByFields()->removeElement($toRemove);
            $toRemove->setNodeSource(null);
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
     * @param Collection<int, NodesSourcesDocuments> $documentsByFields
     *
     * @return NodesSources
     */
    public function setDocumentsByFields(Collection $documentsByFields): NodesSources
    {
        foreach ($this->documentsByFields as $documentsByField) {
            $documentsByField->setNodeSource(null);
        }
        $this->documentsByFields->clear();
        foreach ($documentsByFields as $documentsByField) {
            if (!$this->hasNodesSourcesDocuments($documentsByField)) {
                $this->addDocumentsByFields($documentsByField);
            }
        }

        return $this;
    }

    /**
     * @param NodesSourcesDocuments $nodesSourcesDocuments
     * @return bool
     */
    #[SymfonySerializer\Ignore]
    public function hasNodesSourcesDocuments(NodesSourcesDocuments $nodesSourcesDocuments): bool
    {
        return $this->getDocumentsByFields()->exists(
            function ($key, NodesSourcesDocuments $element) use ($nodesSourcesDocuments) {
                return $nodesSourcesDocuments->getDocument()->getId() !== null &&
                    $element->getDocument()->getId() === $nodesSourcesDocuments->getDocument()->getId() &&
                    $element->getField()->getId() === $nodesSourcesDocuments->getField()->getId();
            }
        );
    }

    /**
     * Used by any NSClass to add directly new documents to source.
     *
     * @param NodesSourcesDocuments $nodesSourcesDocuments
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
    public function getDocumentsByFieldsWithField(NodeTypeField $field): array
    {
        $criteria = Criteria::create();
        $criteria->orderBy(['position' => 'ASC']);
        return $this->getDocumentsByFields()
            ->matching($criteria)
            ->filter(function (NodesSourcesDocuments $element) use ($field) {
                return $element->getField() === $field;
            })
            ->map(function (NodesSourcesDocuments $nodesSourcesDocuments) {
                return $nodesSourcesDocuments->getDocument();
            })
            ->toArray()
        ;
    }

    /**
     * @param string $fieldName
     * @return Document[]
     */
    public function getDocumentsByFieldsWithName(string $fieldName): array
    {
        $criteria = Criteria::create();
        $criteria->orderBy(['position' => 'ASC']);
        return $this->getDocumentsByFields()
            ->matching($criteria)
            ->filter(function (NodesSourcesDocuments $element) use ($fieldName) {
                return $element->getField()->getName() === $fieldName;
            })
            ->map(function (NodesSourcesDocuments $nodesSourcesDocuments) {
                return $nodesSourcesDocuments->getDocument();
            })
            ->toArray()
        ;
    }

    /**
     * Logs related to this node-source.
     *
     * @return Collection<int, Log>
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    /**
     * @param Collection $logs
     * @return $this
     */
    public function setLogs(Collection $logs): NodesSources
    {
        $this->logs = $logs;

        return $this;
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
     * @return NodesSources
     */
    public function setRedirections(Collection $redirections): NodesSources
    {
        $this->redirections = $redirections;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getPublishedAt(): ?\DateTime
    {
        return $this->publishedAt;
    }

    /**
     * @param \DateTime|null $publishedAt
     * @return NodesSources
     */
    public function setPublishedAt(\DateTime $publishedAt = null): NodesSources
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getMetaTitle(): string
    {
        return $this->metaTitle;
    }

    /**
     * @param string|null $metaTitle
     *
     * @return $this
     */
    public function setMetaTitle(?string $metaTitle): NodesSources
    {
        $this->metaTitle = null !== $metaTitle ? trim($metaTitle) : '';

        return $this;
    }

    /**
     * @return string
     */
    public function getMetaKeywords(): string
    {
        return $this->metaKeywords;
    }

    /**
     * @param string|null $metaKeywords
     *
     * @return $this
     */
    public function setMetaKeywords(?string $metaKeywords): NodesSources
    {
        $this->metaKeywords = null !== $metaKeywords ? trim($metaKeywords) : '';

        return $this;
    }

    /**
     * @return string
     */
    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    /**
     * @param string|null $metaDescription
     *
     * @return $this
     */
    public function setMetaDescription(?string $metaDescription): NodesSources
    {
        $this->metaDescription = null !== $metaDescription ? trim($metaDescription) : '';

        return $this;
    }

    /**
     * @return bool
     */
    public function isNoIndex(): bool
    {
        return $this->noIndex;
    }

    /**
     * @param bool $noIndex
     * @return NodesSources
     */
    public function setNoIndex(bool $noIndex): NodesSources
    {
        $this->noIndex = $noIndex;
        return $this;
    }

    /**
     * @return string
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("slug")
     * @Serializer\Groups({"nodes_sources", "nodes_sources_base"})
     */
    #[SymfonySerializer\SerializedName('slug')]
    #[SymfonySerializer\Groups(['nodes_sources', 'nodes_sources_base'])]
    public function getIdentifier(): string
    {
        $urlAlias = $this->getUrlAliases()->first();
        if (false !== $urlAlias && $urlAlias->getAlias() !== '') {
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
     * Get parent node’ source based on the same translation.
     *
     * @return NodesSources|null
     * @Serializer\Exclude
     */
    #[SymfonySerializer\Ignore]
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

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     * @return $this
     */
    public function setTitle(?string $title): NodesSources
    {
        $this->title = null !== $title ? trim($title) : null;
        return $this;
    }

    /**
     * @return TranslationInterface
     */
    public function getTranslation(): TranslationInterface
    {
        if (null === $this->translation) {
            throw new RuntimeException('Node source translation cannot be null.');
        }
        return $this->translation;
    }

    /**
     * @param TranslationInterface $translation
     *
     * @return $this
     */
    public function setTranslation(TranslationInterface $translation): NodesSources
    {
        $this->translation = $translation;
        return $this;
    }

    /**
     * @return string
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"nodes_sources", "nodes_sources_default"})
     * @Serializer\SerializedName("@type")
     */
    #[SymfonySerializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[SymfonySerializer\SerializedName('@type')]
    public function getNodeTypeName(): string
    {
        return 'NodesSources';
    }

    /**
     * Overridden in NS classes.
     *
     * @return bool
     */
    public function isPublishable(): bool
    {
        return $this->getNode()->getNodeType()->isPublishable();
    }

    /**
     * Overridden in NS classes.
     *
     * @return bool
     */
    public function isReachable(): bool
    {
        return $this->getNode()->getNodeType()->isReachable();
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
            // Clear logs before cloning.
            $this->logs->clear();
        }
    }
}
