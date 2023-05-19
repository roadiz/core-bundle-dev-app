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
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Core\AbstractEntities\AbstractDateTimed;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Api\Filter as RoadizFilter;
use RZ\Roadiz\CoreBundle\Api\Filter\CopyrightValidFilter;
use RZ\Roadiz\CoreBundle\Repository\DocumentRepository;
use RZ\Roadiz\Documents\Models\AdvancedDocumentInterface;
use RZ\Roadiz\Documents\Models\DisplayableInterface;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\DocumentTrait;
use RZ\Roadiz\Documents\Models\FileHashInterface;
use RZ\Roadiz\Documents\Models\FolderInterface;
use RZ\Roadiz\Documents\Models\HasThumbnailInterface;
use RZ\Roadiz\Documents\Models\TimeableInterface;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * Documents entity represent a file on server with datetime and naming.
 */
#[
    ORM\Entity(repositoryClass: DocumentRepository::class),
    ORM\Table(name: "documents"),
    ORM\Index(columns: ["created_at"], name: "document_created_at"),
    ORM\Index(columns: ["updated_at"], name: "document_updated_at"),
    ORM\Index(columns: ["raw"]),
    ORM\Index(columns: ["raw", "created_at"], name: "document_raw_created_at"),
    ORM\Index(columns: ["private"]),
    ORM\Index(columns: ["filename"], name: "document_filename"),
    ORM\Index(columns: ["file_hash"], name: "document_file_hash"),
    ORM\Index(columns: ["file_hash_algorithm"], name: "document_hash_algorithm"),
    ORM\Index(columns: ["file_hash", "file_hash_algorithm"], name: "document_file_hash_algorithm"),
    ORM\Index(columns: ["embedId"], name: "document_embed_id"),
    ORM\Index(columns: ["embedId", "embedPlatform"], name: "document_embed_platform_id"),
    ORM\Index(columns: ["embedPlatform"], name: "document_embed_platform"),
    ORM\Index(columns: ["raw", "private"]),
    ORM\Index(columns: ["duration"], name: "document_duration"),
    ORM\Index(columns: ["filesize"], name: "document_filesize"),
    ORM\Index(columns: ["imageWidth"], name: "document_image_width"),
    ORM\Index(columns: ["imageHeight"], name: "document_image_height"),
    ORM\Index(columns: ["mime_type"]),
    Serializer\ExclusionPolicy("all"),
    ApiFilter(PropertyFilter::class),
    ApiFilter(BaseFilter\OrderFilter::class, properties: [
        "createdAt",
        "updatedAt",
        "copyrightValidSince",
        "copyrightValidUntil",
        "filesize"
    ]),
    ApiFilter(BaseFilter\DateFilter::class, properties: [
        "createdAt",
        "updatedAt",
        "copyrightValidSince" => "include_null_before",
        "copyrightValidUntil" => "include_null_after"
    ]),
    ApiFilter(CopyrightValidFilter::class)
]
class Document extends AbstractDateTimed implements AdvancedDocumentInterface, HasThumbnailInterface, TimeableInterface, DisplayableInterface, FileHashInterface
{
    use DocumentTrait;

    /**
     * @var \DateTime|null Null value is included in before filters
     */
    #[ORM\Column(name: 'copyright_valid_since', type: 'datetime', nullable: true)]
    #[SymfonySerializer\Groups(['document_copyright'])]
    #[Serializer\Groups(['document_copyright'])]
    protected ?\DateTime $copyrightValidSince = null;

    /**
     * @var \DateTime|null Null value is always included in after filters
     */
    #[ORM\Column(name: 'copyright_valid_until', type: 'datetime', nullable: true)]
    #[SymfonySerializer\Groups(['document_copyright'])]
    #[Serializer\Groups(['document_copyright'])]
    protected ?\DateTime $copyrightValidUntil = null;

    #[ORM\ManyToOne(
        targetEntity: Document::class,
        cascade: ['all'],
        fetch: 'EXTRA_LAZY',
        inversedBy: 'downscaledDocuments'
    )]
    #[ORM\JoinColumn(name: 'raw_document', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    protected ?DocumentInterface $rawDocument = null;

    #[
        SymfonySerializer\Ignore,
        Serializer\Groups(["document"]),
        Serializer\Type("bool"),
        ORM\Column(name: 'raw', type: 'boolean', nullable: false, options: ['default' => false])
    ]
    protected bool $raw = false;

    #[ORM\Column(name: 'embedId', type: 'string', unique: false, nullable: true)]
    #[SymfonySerializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Type("string")]
    protected ?string $embedId = null;

    #[ORM\Column(name: 'file_hash', type: 'string', length: 64, unique: false, nullable: true)]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    #[Serializer\Type('string')]
    protected ?string $fileHash = null;

    #[ORM\Column(name: 'file_hash_algorithm', type: 'string', length: 15, unique: false, nullable: true)]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    #[Serializer\Type('string')]
    protected ?string $fileHashAlgorithm = null;

    #[ApiFilter(BaseFilter\SearchFilter::class, strategy: "exact")]
    #[ApiFilter(RoadizFilter\NotFilter::class)]
    #[ORM\Column(name: 'embedPlatform', type: 'string', unique: false, nullable: true)]
    #[SymfonySerializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Type('string')]
    protected ?string $embedPlatform = null;
    /**
     * @var Collection<int, NodesSourcesDocuments>
     */
    #[ORM\OneToMany(mappedBy: 'document', targetEntity: NodesSourcesDocuments::class)]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    protected Collection $nodesSourcesByFields;
    /**
     * @var Collection<int, TagTranslationDocuments>
     */
    #[ORM\OneToMany(mappedBy: 'document', targetEntity: TagTranslationDocuments::class)]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    protected Collection $tagTranslations;
    /**
     * @var Collection<int, AttributeDocuments>
     */
    #[ORM\OneToMany(mappedBy: 'document', targetEntity: AttributeDocuments::class)]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    protected Collection $attributeDocuments;
    /**
     * @var Collection<int, CustomFormFieldAttribute>
     */
    #[ORM\ManyToMany(targetEntity: CustomFormFieldAttribute::class, mappedBy: 'documents')]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    protected Collection $customFormFieldAttributes;
    /**
     * @var Collection<int, FolderInterface>
     */
    #[ORM\JoinTable(name: 'documents_folders')]
    #[ORM\ManyToMany(targetEntity: Folder::class, mappedBy: 'documents')]
    #[SymfonySerializer\Ignore]
    protected Collection $folders;
    /**
     * @var Collection<int, DocumentTranslation>
     */
    #[ORM\OneToMany(
        mappedBy: 'document',
        targetEntity: DocumentTranslation::class,
        fetch: 'EAGER',
        orphanRemoval: true
    )]
    #[SymfonySerializer\Ignore]
    #[Serializer\Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Type('ArrayCollection<RZ\Roadiz\CoreBundle\Entity\DocumentTranslation>')]
    protected Collection $documentTranslations;
    /**
     * @var string|null
     */
    #[ApiFilter(BaseFilter\SearchFilter::class, strategy: "partial")]
    #[ORM\Column(name: 'filename', type: 'string', nullable: true)]
    #[SymfonySerializer\Ignore]
    #[Serializer\Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Type('string')]
    private ?string $filename = null;
    /**
     * @var string|null
     */
    #[ApiFilter(BaseFilter\SearchFilter::class, strategy: "exact")]
    #[ApiFilter(RoadizFilter\NotFilter::class)]
    #[ORM\Column(name: 'mime_type', type: 'string', nullable: true)]
    #[SymfonySerializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Type('string')]
    private ?string $mimeType = null;
    /**
     * @var Collection<int, DocumentInterface>
     */
    #[ORM\OneToMany(mappedBy: 'rawDocument', targetEntity: Document::class, fetch: 'EXTRA_LAZY')]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    private Collection $downscaledDocuments;
    /**
     * @var string
     */
    #[ORM\Column(type: 'string')]
    #[SymfonySerializer\Ignore]
    #[Serializer\Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Type('string')]
    private string $folder = '';
    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    #[SymfonySerializer\Ignore]
    #[Serializer\Groups(['document', 'document_private', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Type('bool')]
    private bool $private = false;
    /**
     * @var integer
     */
    #[ORM\Column(name: 'imageWidth', type: 'integer', nullable: false, options: ['default' => 0])]
    #[SymfonySerializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Type('int')]
    private int $imageWidth = 0;
    /**
     * @var integer
     */
    #[ORM\Column(name: 'imageHeight', type: 'integer', nullable: false, options: ['default' => 0])]
    #[SymfonySerializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Type('int')]
    private int $imageHeight = 0;
    /**
     * @var integer
     */
    #[ORM\Column(name: 'duration', type: 'integer', nullable: false, options: ['default' => 0])]
    #[SymfonySerializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Type('int')]
    private int $mediaDuration = 0;
    /**
     * @var string|null
     */
    #[ORM\Column(name: 'average_color', type: 'string', length: 7, unique: false, nullable: true)]
    #[SymfonySerializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Type('string')]
    private ?string $imageAverageColor = null;
    /**
     * @var int|null The filesize in bytes.
     */
    #[ORM\Column(name: 'filesize', type: 'integer', unique: false, nullable: true)]
    #[SymfonySerializer\Groups(['document_filesize'])]
    #[Serializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Type('int')]
    private ?int $filesize = null;

    /**
     * @var Collection<int, DocumentInterface>
     */
    #[ORM\OneToMany(mappedBy: 'original', targetEntity: Document::class, fetch: 'EXTRA_LAZY')]
    #[SymfonySerializer\Ignore]
    #[Serializer\Groups(['document_thumbnails'])]
    #[Serializer\Type('ArrayCollection<RZ\Roadiz\CoreBundle\Entity\Document>')]
    private Collection $thumbnails;

    /**
     * @var HasThumbnailInterface|null
     */
    #[ORM\ManyToOne(targetEntity: Document::class, fetch: 'EXTRA_LAZY', inversedBy: 'thumbnails')]
    #[ORM\JoinColumn(name: 'original', nullable: true, onDelete: 'SET NULL')]
    #[SymfonySerializer\Groups(['document_original'])]
    #[SymfonySerializer\MaxDepth(1)]
    #[Serializer\Groups(['document_original'])]
    #[Serializer\MaxDepth(1)]
    #[Serializer\Type('RZ\Roadiz\CoreBundle\Entity\Document')]
    private ?HasThumbnailInterface $original = null;

    public function __construct()
    {
        $this->initAbstractDateTimed();
        $this->initDocumentTrait();

        $this->folders = new ArrayCollection();
        $this->downscaledDocuments = new ArrayCollection();
        $this->documentTranslations = new ArrayCollection();
        $this->nodesSourcesByFields = new ArrayCollection();
        $this->tagTranslations = new ArrayCollection();
        $this->attributeDocuments = new ArrayCollection();
        $this->customFormFieldAttributes = new ArrayCollection();
        $this->thumbnails = new ArrayCollection();
    }

    /**
     * @return string|null
     */
    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): static
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    /**
     * @return string
     */
    public function getFolder(): string
    {
        return $this->folder;
    }

    public function setFolder(string $folder): static
    {
        $this->folder = $folder;
        return $this;
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): static
    {
        $this->private = $private;
        if (null !== $raw = $this->getRawDocument()) {
            $raw->setPrivate($private);
        }

        return $this;
    }

    #[SymfonySerializer\Ignore]
    public function getRawDocument(): ?DocumentInterface
    {
        return $this->rawDocument;
    }

    public function setRawDocument(DocumentInterface $rawDocument = null): static
    {
        if (null === $rawDocument || $rawDocument instanceof Document) {
            $this->rawDocument = $rawDocument;
        }
        return $this;
    }

    /**
     * @return Collection<int, NodesSourcesDocuments>
     */
    #[SymfonySerializer\Ignore]
    public function getNodesSourcesByFields(): Collection
    {
        return $this->nodesSourcesByFields;
    }

    /**
     * @return Collection<int, TagTranslationDocuments>
     */
    #[SymfonySerializer\Ignore]
    public function getTagTranslations(): Collection
    {
        return $this->tagTranslations;
    }

    /**
     * @return Collection<int, AttributeDocuments>
     */
    #[SymfonySerializer\Ignore]
    public function getAttributeDocuments(): Collection
    {
        return $this->attributeDocuments;
    }

    public function addFolder(FolderInterface $folder): static
    {
        if (!$this->getFolders()->contains($folder)) {
            $this->folders->add($folder);
            $folder->addDocument($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, FolderInterface>
     */
    #[SymfonySerializer\Ignore]
    public function getFolders(): Collection
    {
        return $this->folders;
    }

    public function setFolders(Collection $folders): static
    {
        $this->folders = $folders;

        return $this;
    }

    public function removeFolder(FolderInterface $folder): static
    {
        if ($this->getFolders()->contains($folder)) {
            $this->folders->removeElement($folder);
            $folder->removeDocument($this);
        }

        return $this;
    }

    /**
     * @param TranslationInterface $translation
     * @return Collection<int, DocumentTranslation>
     */
    #[SymfonySerializer\Ignore]
    public function getDocumentTranslationsByTranslation(TranslationInterface $translation): Collection
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('translation', $translation));

        return $this->documentTranslations->matching($criteria);
    }

    /**
     * @param DocumentTranslation $documentTranslation
     * @return $this
     */
    public function addDocumentTranslation(DocumentTranslation $documentTranslation): static
    {
        if (!$this->getDocumentTranslations()->contains($documentTranslation)) {
            $this->documentTranslations->add($documentTranslation);
        }

        return $this;
    }

    /**
     * @return Collection<int, DocumentTranslation>
     */
    public function getDocumentTranslations(): Collection
    {
        return $this->documentTranslations;
    }

    /**
     * @return bool
     */
    #[SymfonySerializer\Ignore]
    public function hasTranslations(): bool
    {
        return $this->getDocumentTranslations()->count() > 0;
    }

    /**
     * Is document a raw one.
     *
     * @return bool
     */
    public function isRaw(): bool
    {
        return $this->raw;
    }

    public function setRaw(bool $raw): static
    {
        $this->raw = $raw;
        return $this;
    }

    /**
     * Gets the downscaledDocument.
     *
     * @return DocumentInterface|null
     */
    #[SymfonySerializer\Ignore]
    public function getDownscaledDocument(): ?DocumentInterface
    {
        return $this->downscaledDocuments->first() ?: null;
    }

    /**
     * @return float|null
     */
    #[SymfonySerializer\Ignore]
    public function getImageRatio(): ?float
    {
        if ($this->getImageWidth() > 0 && $this->getImageHeight() > 0) {
            return $this->getImageWidth() / $this->getImageHeight();
        }
        return null;
    }

    /**
     * @return int
     */
    public function getImageWidth(): int
    {
        return $this->imageWidth;
    }

    public function setImageWidth(int $imageWidth): static
    {
        $this->imageWidth = $imageWidth;

        return $this;
    }

    /**
     * @return int
     */
    public function getImageHeight(): int
    {
        return $this->imageHeight;
    }

    public function setImageHeight(int $imageHeight): static
    {
        $this->imageHeight = $imageHeight;

        return $this;
    }

    /**
     * @return int
     */
    public function getMediaDuration(): int
    {
        return $this->mediaDuration;
    }

    public function setMediaDuration(int $duration): static
    {
        $this->mediaDuration = $duration;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getImageAverageColor(): ?string
    {
        return $this->imageAverageColor;
    }

    public function setImageAverageColor(?string $imageAverageColor): static
    {
        $this->imageAverageColor = $imageAverageColor;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getFilesize(): ?int
    {
        return $this->filesize;
    }

    public function setFilesize(?int $filesize): static
    {
        $this->filesize = $filesize;
        return $this;
    }

    #[
        Serializer\Groups(["document", "document_display", "nodes_sources", "tag", "attribute"]),
        Serializer\Type("string"),
        Serializer\VirtualProperty,
        Serializer\SerializedName("alt"),
        SymfonySerializer\Groups(["document", "document_display", "nodes_sources", "tag", "attribute"]),
        SymfonySerializer\SerializedName("alt"),
    ]
    public function getAlternativeText(): string
    {
        $documentTranslation = $this->getDocumentTranslations()->first();
        return $documentTranslation && !empty($documentTranslation->getName()) ?
            $documentTranslation->getName() :
            $this->getFilename();
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->rawDocument = null;
        }
    }

    /**
     * @return bool
     */
    #[SymfonySerializer\Groups(['document'])]
    #[Serializer\Groups(['document'])]
    #[Serializer\VirtualProperty]
    public function isThumbnail(): bool
    {
        return $this->getOriginal() !== null;
    }

    /**
     * @return HasThumbnailInterface|null
     */
    #[SymfonySerializer\Ignore]
    public function getOriginal(): ?HasThumbnailInterface
    {
        return $this->original;
    }

    public function setOriginal(?HasThumbnailInterface $original): static
    {
        if (null === $original || ($original !== $this && $original instanceof Document)) {
            $this->original = $original;
        }

        return $this;
    }

    /**
     * @return bool
     */
    #[SymfonySerializer\Groups(['document'])]
    #[Serializer\Groups(['document'])]
    #[Serializer\VirtualProperty]
    public function hasThumbnails(): bool
    {
        return $this->getThumbnails()->count() > 0;
    }

    /**
     * @return Collection
     */
    public function getThumbnails(): Collection
    {
        return $this->thumbnails;
    }

    public function setThumbnails(Collection $thumbnails): static
    {
        if ($this->thumbnails->count()) {
            foreach ($this->thumbnails as $thumbnail) {
                if ($thumbnail instanceof HasThumbnailInterface) {
                    $thumbnail->setOriginal(null);
                }
            }
        }
        $this->thumbnails = $thumbnails->filter(function (DocumentInterface $thumbnail) {
            return $thumbnail !== $this;
        });
        foreach ($this->thumbnails as $thumbnail) {
            if ($thumbnail instanceof HasThumbnailInterface) {
                $thumbnail->setOriginal($this);
            }
        }

        return $this;
    }

    /**
     * @return DocumentInterface|null
     */
    #[SymfonySerializer\Groups(['document_thumbnails'])]
    #[SymfonySerializer\SerializedName('thumbnail')]
    #[SymfonySerializer\MaxDepth(1)]
    #[Serializer\MaxDepth(1)]
    public function getFirstThumbnail(): ?DocumentInterface
    {
        if ($this->isEmbed() || !$this->isImage()) {
            return $this->getThumbnails()->first() ?: null;
        }
        return null;
    }

    /**
     * @return bool
     */
    public function needsThumbnail(): bool
    {
        return !$this->isProcessable();
    }

    /**
     * @return string|null
     */
    public function getFileHash(): ?string
    {
        return $this->fileHash;
    }

    public function setFileHash(?string $hash): static
    {
        $this->fileHash = $hash;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFileHashAlgorithm(): ?string
    {
        return $this->fileHashAlgorithm;
    }

    public function setFileHashAlgorithm(?string $algorithm): static
    {
        $this->fileHashAlgorithm = $algorithm;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getCopyrightValidSince(): ?\DateTime
    {
        return $this->copyrightValidSince;
    }

    public function setCopyrightValidSince(?\DateTime $copyrightValidSince): static
    {
        $this->copyrightValidSince = $copyrightValidSince;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getCopyrightValidUntil(): ?\DateTime
    {
        return $this->copyrightValidUntil;
    }

    public function setCopyrightValidUntil(?\DateTime $copyrightValidUntil): static
    {
        $this->copyrightValidUntil = $copyrightValidUntil;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if (!empty($this->getFilename())) {
            return $this->getFilename();
        }
        $translation = $this->getDocumentTranslations()->first();
        if (false !== $translation && !empty($translation->getName())) {
            return $translation->getName();
        }
        if (!empty($this->getEmbedPlatform())) {
            return $this->getEmbedPlatform() . ' (' . $this->getEmbedId() . ')';
        }
        return (string) $this->getId();
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename ?? '';
    }

    public function setFilename(string $filename): static
    {
        $this->filename = StringHandler::cleanForFilename($filename);

        return $this;
    }


    public function getEmbedPlatform(): ?string
    {
        return $this->embedPlatform;
    }

    public function setEmbedPlatform(?string $embedPlatform): static
    {
        $this->embedPlatform = $embedPlatform;
        return $this;
    }

    public function getEmbedId(): ?string
    {
        return $this->embedId;
    }

    public function setEmbedId(?string $embedId): static
    {
        $this->embedId = $embedId;
        return $this;
    }
}
