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
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Core\AbstractEntities\AbstractDateTimed;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Api\Filter as RoadizFilter;
use RZ\Roadiz\CoreBundle\Api\Filter\CopyrightValidFilter;
use RZ\Roadiz\CoreBundle\Repository\DocumentRepository;
use RZ\Roadiz\Documents\Models\AdvancedDocumentInterface;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\DocumentTrait;
use RZ\Roadiz\Documents\Models\FileHashInterface;
use RZ\Roadiz\Documents\Models\FolderInterface;
use RZ\Roadiz\Documents\Models\HasThumbnailInterface;
use RZ\Roadiz\Documents\Models\TimeableInterface;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Documents entity represent a file on server with datetime and naming.
 */
#[
    ORM\Entity(repositoryClass: DocumentRepository::class),
    ORM\Table(name: 'documents'),
    ORM\Index(columns: ['created_at'], name: 'document_created_at'),
    ORM\Index(columns: ['updated_at'], name: 'document_updated_at'),
    ORM\Index(columns: ['raw']),
    ORM\Index(columns: ['raw', 'created_at'], name: 'document_raw_created_at'),
    ORM\Index(columns: ['private']),
    ORM\Index(columns: ['filename'], name: 'document_filename'),
    ORM\Index(columns: ['file_hash'], name: 'document_file_hash'),
    ORM\Index(columns: ['file_hash_algorithm'], name: 'document_hash_algorithm'),
    ORM\Index(columns: ['file_hash', 'file_hash_algorithm'], name: 'document_file_hash_algorithm'),
    ORM\Index(columns: ['embedId'], name: 'document_embed_id'),
    ORM\Index(columns: ['embedId', 'embedPlatform'], name: 'document_embed_platform_id'),
    ORM\Index(columns: ['embedPlatform'], name: 'document_embed_platform'),
    ORM\Index(columns: ['raw', 'private']),
    ORM\Index(columns: ['duration'], name: 'document_duration'),
    ORM\Index(columns: ['filesize'], name: 'document_filesize'),
    ORM\Index(columns: ['imageWidth'], name: 'document_image_width'),
    ORM\Index(columns: ['imageHeight'], name: 'document_image_height'),
    ORM\Index(columns: ['mime_type']),
    Serializer\ExclusionPolicy('all'),
    ApiFilter(PropertyFilter::class),
    ApiFilter(BaseFilter\OrderFilter::class, properties: [
        'createdAt',
        'updatedAt',
        'copyrightValidSince',
        'copyrightValidUntil',
        'filesize',
    ]),
    ApiFilter(BaseFilter\DateFilter::class, properties: [
        'createdAt',
        'updatedAt',
        'copyrightValidSince' => 'include_null_before',
        'copyrightValidUntil' => 'include_null_after',
    ]),
    ApiFilter(CopyrightValidFilter::class)
]
class Document extends AbstractDateTimed implements AdvancedDocumentInterface, HasThumbnailInterface, TimeableInterface, FileHashInterface
{
    use DocumentTrait;

    /**
     * @var \DateTime|null Null value is included in before filters
     */
    #[ORM\Column(name: 'copyright_valid_since', type: 'datetime', nullable: true)]
    #[SymfonySerializer\Groups(['document_copyright'])]
    #[Serializer\Groups(['document_copyright'])]
    #[ApiProperty(
        description: 'Document copyright starting date',
    )]
    protected ?\DateTime $copyrightValidSince = null;

    /**
     * @var \DateTime|null Null value is always included in after filters
     */
    #[ORM\Column(name: 'copyright_valid_until', type: 'datetime', nullable: true)]
    #[SymfonySerializer\Groups(['document_copyright'])]
    #[Serializer\Groups(['document_copyright'])]
    #[ApiProperty(
        description: 'Document copyright expiry date',
    )]
    protected ?\DateTime $copyrightValidUntil = null;

    /**
     * @var string|null Image crop alignment.
     *
     * The possible values are:
     *
     * top-left
     * top
     * top-right
     * left
     * center (default)
     * right
     * bottom-left
     * bottom
     * bottom-right
     */
    #[ORM\Column(name: 'image_crop_alignment', type: 'string', length: 12, nullable: true)]
    #[SymfonySerializer\Ignore]
    #[Assert\Length(max: 12)]
    #[Assert\Choice(choices: [
        'top-left',
        'top',
        'top-right',
        'left',
        'center',
        'right',
        'bottom-left',
        'bottom',
        'bottom-right',
    ])]
    protected ?string $imageCropAlignment = null;

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
        Serializer\Groups(['document']),
        Serializer\Type('bool'),
        ORM\Column(name: 'raw', type: 'boolean', nullable: false, options: ['default' => false])
    ]
    protected bool $raw = false;

    #[ORM\Column(name: 'embedId', type: 'string', length: 250, unique: false, nullable: true)]
    #[SymfonySerializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Type('string')]
    #[ApiProperty(
        description: 'Embed ID on external platforms',
        example: 'FORSwsjtQSE',
    )]
    #[Assert\Length(max: 250)]
    protected ?string $embedId = null;

    #[ORM\Column(name: 'file_hash', type: 'string', length: 64, unique: false, nullable: true)]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    #[Serializer\Type('string')]
    #[Assert\Length(max: 64)]
    protected ?string $fileHash = null;

    #[ORM\Column(name: 'file_hash_algorithm', type: 'string', length: 15, unique: false, nullable: true)]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    #[Serializer\Type('string')]
    #[Assert\Length(max: 15)]
    protected ?string $fileHashAlgorithm = null;

    #[ApiFilter(BaseFilter\SearchFilter::class, strategy: 'exact')]
    #[ApiFilter(RoadizFilter\NotFilter::class)]
    #[ORM\Column(name: 'embedPlatform', type: 'string', length: 100, unique: false, nullable: true)]
    #[SymfonySerializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Type('string')]
    #[Assert\Length(max: 100)]
    #[ApiProperty(
        description: 'Embed platform name',
        example: 'youtube',
    )]
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
        orphanRemoval: true
    )]
    #[SymfonySerializer\Ignore]
    #[Serializer\Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Type('ArrayCollection<RZ\Roadiz\CoreBundle\Entity\DocumentTranslation>')]
    protected Collection $documentTranslations;
    #[ApiFilter(BaseFilter\SearchFilter::class, strategy: 'partial')]
    #[ORM\Column(name: 'filename', type: 'string', length: 250, nullable: true)]
    #[SymfonySerializer\Ignore]
    #[Serializer\Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Type('string')]
    #[Assert\Length(max: 250)]
    private ?string $filename = null;
    #[ApiFilter(BaseFilter\SearchFilter::class, strategy: 'exact')]
    #[ApiFilter(RoadizFilter\NotFilter::class)]
    #[ORM\Column(name: 'mime_type', type: 'string', length: 255, nullable: true)]
    #[SymfonySerializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Type('string')]
    #[Assert\Length(max: 255)]
    #[ApiProperty(
        description: 'Document file mime type',
        example: 'image/jpeg',
    )]
    private ?string $mimeType = null;
    /**
     * @var Collection<int, DocumentInterface>
     */
    #[ORM\OneToMany(mappedBy: 'rawDocument', targetEntity: Document::class, fetch: 'EXTRA_LAZY')]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    private Collection $downscaledDocuments;
    #[ORM\Column(type: 'string', length: 12, nullable: true)]
    #[SymfonySerializer\Ignore]
    #[Assert\Length(max: 12)]
    #[Serializer\Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Type('string')]
    private ?string $folder = null;
    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    #[SymfonySerializer\Ignore]
    #[Serializer\Groups(['document', 'document_private', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Type('bool')]
    private bool $private = false;
    #[ORM\Column(name: 'imageWidth', type: Types::SMALLINT, nullable: false, options: ['default' => 0])]
    #[SymfonySerializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Type('int')]
    #[ApiProperty(
        description: 'When document has visual size: width in pixels',
        example: '1280',
    )]
    private int $imageWidth = 0;
    #[ORM\Column(name: 'imageHeight', type: Types::SMALLINT, nullable: false, options: ['default' => 0])]
    #[SymfonySerializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Type('int')]
    #[ApiProperty(
        description: 'When document has visual size: height in pixels',
        example: '800',
    )]
    private int $imageHeight = 0;
    #[ORM\Column(name: 'duration', type: Types::INTEGER, nullable: false, options: ['default' => 0])]
    #[SymfonySerializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Type('int')]
    #[ApiProperty(
        description: 'When document is audio or video: duration in seconds',
        example: '300',
    )]
    private int $mediaDuration = 0;
    #[ORM\Column(name: 'average_color', type: 'string', length: 7, unique: false, nullable: true)]
    #[SymfonySerializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Type('string')]
    #[Assert\Length(max: 7)]
    #[ApiProperty(
        description: 'When document is image: average color in hexadecimal format',
        example: '#ffffff'
    )]
    private ?string $imageAverageColor = null;
    /**
     * @var int|null the filesize in bytes
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

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getFolder(): string
    {
        return $this->folder ?? 'documents';
    }

    /**
     * @internal You should use DocumentFactory to generate a document folder
     */
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

    public function setRawDocument(?DocumentInterface $rawDocument = null): static
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
     * @return Collection<int, DocumentTranslation>
     */
    #[SymfonySerializer\Ignore]
    public function getDocumentTranslationsByTranslation(TranslationInterface $translation): Collection
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('translation', $translation));

        return $this->documentTranslations->matching($criteria);
    }

    #[SymfonySerializer\Ignore]
    public function getDocumentTranslationsByDefaultTranslation(): ?DocumentTranslation
    {
        return $this->documentTranslations->findFirst(function (int $key, DocumentTranslation $documentTranslation) {
            return $documentTranslation->getTranslation()->isDefaultTranslation();
        });
    }

    /**
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

    #[SymfonySerializer\Ignore]
    public function hasTranslations(): bool
    {
        return $this->getDocumentTranslations()->count() > 0;
    }

    /**
     * Is document a raw one.
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
     */
    #[SymfonySerializer\Ignore]
    public function getDownscaledDocument(): ?DocumentInterface
    {
        return $this->downscaledDocuments->first() ?: null;
    }

    #[SymfonySerializer\Ignore]
    public function getImageRatio(): ?float
    {
        if ($this->getImageWidth() > 0 && $this->getImageHeight() > 0) {
            return $this->getImageWidth() / $this->getImageHeight();
        }

        return null;
    }

    public function getImageWidth(): int
    {
        return $this->imageWidth;
    }

    public function setImageWidth(int $imageWidth): static
    {
        $this->imageWidth = $imageWidth;

        return $this;
    }

    public function getImageHeight(): int
    {
        return $this->imageHeight;
    }

    public function setImageHeight(int $imageHeight): static
    {
        $this->imageHeight = $imageHeight;

        return $this;
    }

    public function getMediaDuration(): int
    {
        return $this->mediaDuration;
    }

    public function setMediaDuration(int $duration): static
    {
        $this->mediaDuration = $duration;

        return $this;
    }

    public function getImageAverageColor(): ?string
    {
        return $this->imageAverageColor;
    }

    public function setImageAverageColor(?string $imageAverageColor): static
    {
        $this->imageAverageColor = $imageAverageColor;

        return $this;
    }

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
        Serializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute']),
        Serializer\Type('string'),
        Serializer\VirtualProperty,
        Serializer\SerializedName('alt'),
        SymfonySerializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute']),
        SymfonySerializer\SerializedName('alt'),
        ApiProperty(
            description: 'Document alternative text, for img HTML tag. Image is decorative if this is NULL or empty.',
            writable: false,
        )
    ]
    public function getAlternativeText(): ?string
    {
        $documentTranslation = $this->getDocumentTranslations()->first();

        return $documentTranslation && !empty($documentTranslation->getName()) ?
            $documentTranslation->getName() :
            null;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->rawDocument = null;
        }
    }

    #[SymfonySerializer\Groups(['document'])]
    #[SymfonySerializer\SerializedName('isThumbnail')] // to avoid conflict with thumbnail property
    #[Serializer\Groups(['document'])]
    #[Serializer\VirtualProperty]
    public function isThumbnail(): bool
    {
        return null !== $this->getOriginal();
    }

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

    #[SymfonySerializer\Groups(['document'])]
    #[Serializer\Groups(['document'])]
    #[Serializer\VirtualProperty]
    public function hasThumbnails(): bool
    {
        return $this->getThumbnails()->count() > 0;
    }

    public function getThumbnails(): Collection
    {
        // Filter private thumbnails
        return $this->thumbnails->filter(function (DocumentInterface $thumbnail) {
            return !$thumbnail->isPrivate();
        });
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

    public function needsThumbnail(): bool
    {
        return !$this->isProcessable();
    }

    public function getFileHash(): ?string
    {
        return $this->fileHash;
    }

    public function setFileHash(?string $hash): static
    {
        $this->fileHash = $hash;

        return $this;
    }

    public function getFileHashAlgorithm(): ?string
    {
        return $this->fileHashAlgorithm;
    }

    public function setFileHashAlgorithm(?string $algorithm): static
    {
        $this->fileHashAlgorithm = $algorithm;

        return $this;
    }

    public function getCopyrightValidSince(): ?\DateTime
    {
        return $this->copyrightValidSince;
    }

    public function setCopyrightValidSince(?\DateTime $copyrightValidSince): static
    {
        $this->copyrightValidSince = $copyrightValidSince;

        return $this;
    }

    public function getCopyrightValidUntil(): ?\DateTime
    {
        return $this->copyrightValidUntil;
    }

    public function setCopyrightValidUntil(?\DateTime $copyrightValidUntil): static
    {
        $this->copyrightValidUntil = $copyrightValidUntil;

        return $this;
    }

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
            return $this->getEmbedPlatform().' ('.$this->getEmbedId().')';
        }

        return (string) $this->getId();
    }

    public function getFilename(): string
    {
        return $this->filename ?? '';
    }

    public function setFilename(string $filename, bool $sanitize = true): static
    {
        $this->filename = $sanitize ? StringHandler::cleanForFilename($filename) : $filename;

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

    public function getImageCropAlignment(): ?string
    {
        return $this->imageCropAlignment;
    }

    public function setImageCropAlignment(?string $imageCropAlignment): Document
    {
        $this->imageCropAlignment = $imageCropAlignment;

        return $this;
    }
}
