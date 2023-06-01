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
use RZ\Roadiz\Core\AbstractEntities\AbstractDateTimedPositioned;
use RZ\Roadiz\Core\AbstractEntities\LeafInterface;
use RZ\Roadiz\Core\AbstractEntities\LeafTrait;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Repository\FolderRepository;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\FolderInterface;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Folders entity represent a directory on server with datetime and naming.
 */
#[
    ORM\Entity(repositoryClass: FolderRepository::class),
    ORM\HasLifecycleCallbacks,
    ORM\Table(name: "folders"),
    ORM\Index(columns: ["visible"]),
    ORM\Index(columns: ["locked"]),
    ORM\Index(columns: ["position"]),
    ORM\Index(columns: ["created_at"]),
    ORM\Index(columns: ["updated_at"]),
    ORM\Index(columns: ["parent_id", "position"], name: "folder_parent_position"),
    ORM\Index(columns: ["visible", "position"], name: "folder_visible_position"),
    ORM\Index(columns: ["parent_id", "visible"], name: "folder_parent_visible"),
    ORM\Index(columns: ["parent_id", "visible", "position"], name: "folder_parent_visible_position"),
    UniqueEntity(fields: ["folderName"]),
    ApiFilter(PropertyFilter::class),
    ApiFilter(BaseFilter\OrderFilter::class, properties: [
        "position",
        "createdAt",
        "updatedAt"
    ])
]
class Folder extends AbstractDateTimedPositioned implements FolderInterface, LeafInterface
{
    use LeafTrait;

    /**
     * @var Folder|null
     */
    #[ApiFilter(BaseFilter\SearchFilter::class, properties: [
        "parent.id" => "exact",
        "parent.folderName" => "exact"
    ])]
    #[ORM\ManyToOne(targetEntity: Folder::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Serializer\Groups(['folder_parent'])]
    #[SymfonySerializer\Groups(['folder_parent'])]
    #[SymfonySerializer\MaxDepth(1)]
    protected ?Folder $parent = null;

    /**
     * @var Collection<int, Folder>
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Folder::class, orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[SymfonySerializer\Groups(['folder_children'])]
    #[Serializer\Groups(['folder_children'])]
    #[SymfonySerializer\MaxDepth(1)]
    protected Collection $children;

    /**
     * @var Collection<int, DocumentInterface>
     */
    #[ORM\JoinTable(name: 'documents_folders')]
    #[ORM\JoinColumn(name: 'folder_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'document_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: DocumentInterface::class, inversedBy: 'folders')]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    /** @phpstan-ignore-next-line */
    protected Collection $documents;

    /**
     * @var string
     * @Serializer\Groups({"folder", "folder_color"})
     * @Serializer\Type("string")
     */
    #[ORM\Column(
        name: 'color',
        type: 'string',
        length: 7,
        unique: false,
        nullable: false,
        options: ['default' => '#000000']
    )]
    #[SymfonySerializer\Groups(['folder', 'folder_color'])]
    protected string $color = '#000000';

    #[ApiFilter(BaseFilter\SearchFilter::class, strategy: "partial")]
    #[ORM\Column(name: 'folder_name', type: 'string', unique: true, nullable: false)]
    #[Serializer\Groups(['folder', 'document_folders'])]
    #[SymfonySerializer\Groups(['folder', 'document_folders'])]
    #[SymfonySerializer\SerializedName('slug')]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Length(max: 250)]
    private string $folderName = '';

    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    private string $dirtyFolderName = '';

    #[ApiFilter(BaseFilter\BooleanFilter::class)]
    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    #[SymfonySerializer\Groups(['folder', 'document_folders'])]
    #[Serializer\Groups(['folder', 'document_folders'])]
    private bool $visible = true;

    #[ApiFilter(BaseFilter\BooleanFilter::class)]
    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    #[SymfonySerializer\Groups(['folder'])]
    #[Serializer\Groups(['folder'])]
    #[Serializer\Type('bool')]
    private bool $locked = false;

    /**
     * @var Collection<int, FolderTranslation>
     */
    #[ORM\OneToMany(mappedBy: 'folder', targetEntity: FolderTranslation::class, orphanRemoval: true)]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    private Collection $translatedFolders;

    /**
     * Create a new Folder.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->translatedFolders = new ArrayCollection();
        $this->initAbstractDateTimed();
    }

    /**
     * @param DocumentInterface $document
     * @return $this
     */
    public function addDocument(DocumentInterface $document): static
    {
        if (!$this->getDocuments()->contains($document)) {
            $this->documents->add($document);
        }

        return $this;
    }

    /**
     * @return Collection<int, DocumentInterface>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    /**
     * @param DocumentInterface $document
     * @return $this
     */
    public function removeDocument(DocumentInterface $document): static
    {
        if ($this->getDocuments()->contains($document)) {
            $this->documents->removeElement($document);
        }

        return $this;
    }

    /**
     * @return boolean
     */
    public function getVisible(): bool
    {
        return $this->isVisible();
    }

    /**
     * @return boolean
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * @param bool $visible
     * @return $this
     */
    public function setVisible(bool $visible): static
    {
        $this->visible = $visible;
        return $this;
    }

    /**
     * @param TranslationInterface $translation
     * @return Collection<int, FolderTranslation>
     */
    #[SymfonySerializer\Ignore]
    public function getTranslatedFoldersByTranslation(TranslationInterface $translation): Collection
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('translation', $translation));

        return $this->translatedFolders->matching($criteria);
    }

    /**
     * @return string|null
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"folder", "document_folders"})
     */
    #[SymfonySerializer\Groups(['folder', 'document_folders'])]
    public function getName(): ?string
    {
        return $this->getTranslatedFolders()->first() ?
            $this->getTranslatedFolders()->first()->getName() :
            $this->getFolderName();
    }

    /**
     * @return Collection<int, FolderTranslation>
     */
    public function getTranslatedFolders(): Collection
    {
        return $this->translatedFolders;
    }

    /**
     * @param Collection<int, FolderTranslation> $translatedFolders
     * @return $this
     */
    public function setTranslatedFolders(Collection $translatedFolders): static
    {
        $this->translatedFolders = $translatedFolders;
        return $this;
    }

    /**
     * @return string
     */
    public function getFolderName(): string
    {
        return $this->folderName ?? '';
    }

    /**
     * @param string $folderName
     * @return $this
     */
    public function setFolderName(string $folderName): static
    {
        $this->dirtyFolderName = $folderName;
        $this->folderName = StringHandler::slugify($folderName);
        return $this;
    }

    /**
     * @return string
     */
    public function getDirtyFolderName(): string
    {
        return $this->dirtyFolderName;
    }

    /**
     * @param string $dirtyFolderName
     * @return $this
     */
    public function setDirtyFolderName(string $dirtyFolderName): static
    {
        $this->dirtyFolderName = $dirtyFolderName;
        return $this;
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
     * @return Folder
     */
    public function setLocked(bool $locked): Folder
    {
        $this->locked = $locked;
        return $this;
    }

    /**
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * @param string $color
     * @return Folder
     */
    public function setColor(string $color): Folder
    {
        $this->color = $color;
        return $this;
    }

    /**
     * Get folder full path using folder names.
     *
     * @return string
     */
    #[SymfonySerializer\Ignore]
    public function getFullPath(): string
    {
        $parents = $this->getParents();
        $path = [];

        foreach ($parents as $parent) {
            if ($parent instanceof FolderInterface) {
                $path[] = $parent->getFolderName();
            }
        }

        $path[] = $this->getFolderName();

        return implode('/', $path);
    }

    public function setParent(?LeafInterface $parent = null): static
    {
        if ($parent === $this) {
            throw new \InvalidArgumentException('An entity cannot have itself as a parent.');
        }
        if (null !== $parent && !$parent instanceof Folder) {
            throw new \InvalidArgumentException('A folder can only have a folder as a parent.');
        }
        $this->parent = $parent;
        $this->parent?->addChild($this);

        return $this;
    }
}
