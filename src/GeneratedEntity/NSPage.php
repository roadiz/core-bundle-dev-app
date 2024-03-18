<?php

declare(strict_types=1);

/*
 * THIS IS A GENERATED FILE, DO NOT EDIT IT
 * IT WILL BE RECREATED AT EACH NODE-TYPE UPDATE
 */
namespace App\GeneratedEntity;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter as OrmFilter;
use ApiPlatform\Serializer\Filter\PropertyFilter;

/**
 * DO NOT EDIT
 * Generated custom node-source type by Roadiz.
 */
#[
    Gedmo\Loggable(logEntryClass: \RZ\Roadiz\CoreBundle\Entity\UserLogEntry::class),
    ORM\Entity(repositoryClass: \App\GeneratedEntity\Repository\NSPageRepository::class),
    ORM\Table(name: "ns_page"),
    ORM\Index(columns: ["sticky"]),
    ORM\Index(columns: ["stickytest"]),
    ORM\Index(columns: ["layout"]),
    ApiFilter(PropertyFilter::class)
]
class NSPage extends \RZ\Roadiz\CoreBundle\Entity\NodesSources
{
    /**
     * Content.
     * Content.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "content"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        \ApiPlatform\Metadata\ApiProperty(description: "Content: Content"),
        SymfonySerializer\MaxDepth(2),
        Gedmo\Versioned,
        ORM\Column(name: "content", type: "text", nullable: true),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(2),
        Serializer\Type("string")
    ]
    private ?string $content = null;

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string|null $content
     *
     * @return $this
     */
    public function setContent(?string $content): static
    {
        $this->content = null !== $content ?
            (string) $content :
            null;

        return $this;
    }


    /**
     * Sub-title.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "subTitle"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        \ApiPlatform\Metadata\ApiProperty(description: "Sub-title"),
        SymfonySerializer\MaxDepth(2),
        Gedmo\Versioned,
        ORM\Column(
            name: "sub_title",
            type: "string",
            nullable: true,
            length: 250
        ),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(2),
        Serializer\Type("string")
    ]
    private ?string $subTitle = null;

    /**
     * @return string|null
     */
    public function getSubTitle(): ?string
    {
        return $this->subTitle;
    }

    /**
     * @param string|null $subTitle
     *
     * @return $this
     */
    public function setSubTitle(?string $subTitle): static
    {
        $this->subTitle = null !== $subTitle ?
            (string) $subTitle :
            null;

        return $this;
    }


    /**
     * Page color.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "color"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        \ApiPlatform\Metadata\ApiProperty(description: "Page color"),
        SymfonySerializer\MaxDepth(2),
        Gedmo\Versioned,
        ORM\Column(
            name: "color",
            type: "string",
            nullable: true,
            length: 10
        ),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(2),
        Serializer\Type("string")
    ]
    private ?string $color = null;

    /**
     * @return string|null
     */
    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * @param string|null $color
     *
     * @return $this
     */
    public function setColor(?string $color): static
    {
        $this->color = null !== $color ?
            (string) $color :
            null;

        return $this;
    }


    /**
     * Images.
     *
     * (Virtual field, this var is a buffer)
     */
    #[
        Serializer\Exclude,
        SymfonySerializer\SerializedName(serializedName: "images"),
        SymfonySerializer\Groups(["realm_a"]),
        \ApiPlatform\Metadata\ApiProperty(description: "Images"),
        SymfonySerializer\MaxDepth(2)
    ]
    private ?array $images = null;

    /**
     * @return \RZ\Roadiz\CoreBundle\Entity\Document[] Documents array
     */
    #[
        Serializer\Groups(["realm_a"]),
        Serializer\MaxDepth(2),
        Serializer\VirtualProperty,
        Serializer\SerializedName("images"),
        Serializer\Type("array<RZ\Roadiz\CoreBundle\Entity\Document>")
    ]
    public function getImages(): array
    {
        if (null === $this->images) {
            if (null !== $this->objectManager) {
                $this->images = $this->objectManager
                    ->getRepository(\RZ\Roadiz\CoreBundle\Entity\Document::class)
                    ->findByNodeSourceAndFieldName(
                        $this,
                        'images'
                    );
            } else {
                $this->images = [];
            }
        }
        return $this->images;
    }

    /**
     * @param \RZ\Roadiz\CoreBundle\Entity\Document $document
     *
     * @return $this
     */
    public function addImages(\RZ\Roadiz\CoreBundle\Entity\Document $document): static
    {
        if (null !== $this->objectManager) {
            $nodeSourceDocument = new \RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments(
                $this,
                $document
            );
            $nodeSourceDocument->setFieldName('images');
            if (!$this->hasNodesSourcesDocuments($nodeSourceDocument)) {
                $this->objectManager->persist($nodeSourceDocument);
                $this->addDocumentsByFields($nodeSourceDocument);
                $this->images = null;
            }
        }
        return $this;
    }


    /**
     * Header image.
     * Group: Images.
     *
     * (Virtual field, this var is a buffer)
     */
    #[
        Serializer\Exclude,
        SymfonySerializer\SerializedName(serializedName: "headerImage"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_images", "nodes_sources_documents"]),
        \ApiPlatform\Metadata\ApiProperty(description: "Header image"),
        SymfonySerializer\MaxDepth(2)
    ]
    private ?array $headerImage = null;

    /**
     * @return \RZ\Roadiz\CoreBundle\Entity\Document[] Documents array
     */
    #[
        Serializer\Groups(["nodes_sources", "nodes_sources_images", "nodes_sources_documents"]),
        Serializer\MaxDepth(2),
        Serializer\VirtualProperty,
        Serializer\SerializedName("headerImage"),
        Serializer\Type("array<RZ\Roadiz\CoreBundle\Entity\Document>")
    ]
    public function getHeaderImage(): array
    {
        if (null === $this->headerImage) {
            if (null !== $this->objectManager) {
                $this->headerImage = $this->objectManager
                    ->getRepository(\RZ\Roadiz\CoreBundle\Entity\Document::class)
                    ->findByNodeSourceAndFieldName(
                        $this,
                        'header_image'
                    );
            } else {
                $this->headerImage = [];
            }
        }
        return $this->headerImage;
    }

    /**
     * @param \RZ\Roadiz\CoreBundle\Entity\Document $document
     *
     * @return $this
     */
    public function addHeaderImage(\RZ\Roadiz\CoreBundle\Entity\Document $document): static
    {
        if (null !== $this->objectManager) {
            $nodeSourceDocument = new \RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments(
                $this,
                $document
            );
            $nodeSourceDocument->setFieldName('header_image');
            if (!$this->hasNodesSourcesDocuments($nodeSourceDocument)) {
                $this->objectManager->persist($nodeSourceDocument);
                $this->addDocumentsByFields($nodeSourceDocument);
                $this->headerImage = null;
            }
        }
        return $this;
    }


    /**
     * Overtitle.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "overTitle"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        \ApiPlatform\Metadata\ApiProperty(description: "Overtitle"),
        SymfonySerializer\MaxDepth(2),
        Gedmo\Versioned,
        ORM\Column(
            name: "over_title",
            type: "string",
            nullable: true,
            length: 250
        ),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(2),
        Serializer\Type("string")
    ]
    private ?string $overTitle = null;

    /**
     * @return string|null
     */
    public function getOverTitle(): ?string
    {
        return $this->overTitle;
    }

    /**
     * @param string|null $overTitle
     *
     * @return $this
     */
    public function setOverTitle(?string $overTitle): static
    {
        $this->overTitle = null !== $overTitle ?
            (string) $overTitle :
            null;

        return $this;
    }


    /**
     * Pictures.
     * Picture for website.
     * Group: Images.
     *
     * (Virtual field, this var is a buffer)
     */
    #[
        Serializer\Exclude,
        SymfonySerializer\SerializedName(serializedName: "pictures"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_images", "nodes_sources_documents"]),
        \ApiPlatform\Metadata\ApiProperty(description: "Pictures: Picture for website"),
        SymfonySerializer\MaxDepth(2)
    ]
    private ?array $pictures = null;

    /**
     * @return \RZ\Roadiz\CoreBundle\Entity\Document[] Documents array
     */
    #[
        Serializer\Groups(["nodes_sources", "nodes_sources_images", "nodes_sources_documents"]),
        Serializer\MaxDepth(2),
        Serializer\VirtualProperty,
        Serializer\SerializedName("pictures"),
        Serializer\Type("array<RZ\Roadiz\CoreBundle\Entity\Document>")
    ]
    public function getPictures(): array
    {
        if (null === $this->pictures) {
            if (null !== $this->objectManager) {
                $this->pictures = $this->objectManager
                    ->getRepository(\RZ\Roadiz\CoreBundle\Entity\Document::class)
                    ->findByNodeSourceAndFieldName(
                        $this,
                        'pictures'
                    );
            } else {
                $this->pictures = [];
            }
        }
        return $this->pictures;
    }

    /**
     * @param \RZ\Roadiz\CoreBundle\Entity\Document $document
     *
     * @return $this
     */
    public function addPictures(\RZ\Roadiz\CoreBundle\Entity\Document $document): static
    {
        if (null !== $this->objectManager) {
            $nodeSourceDocument = new \RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments(
                $this,
                $document
            );
            $nodeSourceDocument->setFieldName('pictures');
            if (!$this->hasNodesSourcesDocuments($nodeSourceDocument)) {
                $this->objectManager->persist($nodeSourceDocument);
                $this->addDocumentsByFields($nodeSourceDocument);
                $this->pictures = null;
            }
        }
        return $this;
    }


    /**
     * nodeReferencesSources NodesSources direct field buffer.
     * (Virtual field, this var is a buffer)
     *
     * References.
     * Default values: Page
     * @var \App\GeneratedEntity\NSPage[]|null
     */
    #[
        Serializer\Exclude,
        SymfonySerializer\SerializedName(serializedName: "nodeReferences"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default", "nodes_sources_nodes"]),
        \ApiPlatform\Metadata\ApiProperty(description: "References"),
        SymfonySerializer\MaxDepth(2)
    ]
    private ?array $nodeReferencesSources = null;

    /**
     * @return \App\GeneratedEntity\NSPage[] nodeReferences nodes-sources array
     */
    #[
        Serializer\Groups(["nodes_sources", "nodes_sources_default", "nodes_sources_nodes"]),
        Serializer\MaxDepth(2),
        Serializer\VirtualProperty,
        Serializer\SerializedName("nodeReferences"),
        Serializer\Type("array<RZ\Roadiz\CoreBundle\Entity\NodesSources>")
    ]
    public function getNodeReferencesSources(): array
    {
        if (null === $this->nodeReferencesSources) {
            if (null !== $this->objectManager) {
                $this->nodeReferencesSources = $this->objectManager
                    ->getRepository(\App\GeneratedEntity\NSPage::class)
                    ->findByNodesSourcesAndFieldNameAndTranslation(
                        $this,
                        'node_references'
                    );
            } else {
                $this->nodeReferencesSources = [];
            }
        }
        return $this->nodeReferencesSources;
    }

    /**
     * @param \App\GeneratedEntity\NSPage[]|null $nodeReferencesSources
     *
     * @return $this
     */
    public function setNodeReferencesSources(?array $nodeReferencesSources): static
    {
        $this->nodeReferencesSources = $nodeReferencesSources;

        return $this;
    }


    /**
     * Sticky.
     * Group: Boolean.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "sticky"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_boolean"]),
        \ApiPlatform\Metadata\ApiProperty(description: "Sticky"),
        SymfonySerializer\MaxDepth(2),
        ApiFilter(OrmFilter\OrderFilter::class),
        ApiFilter(OrmFilter\BooleanFilter::class),
        Gedmo\Versioned,
        ORM\Column(
            name: "sticky",
            type: "boolean",
            nullable: false,
            options: ["default" => false]
        ),
        Serializer\Groups(["nodes_sources", "nodes_sources_boolean"]),
        Serializer\MaxDepth(2),
        Serializer\Type("bool")
    ]
    private bool $sticky = false;

    /**
     * @return bool
     */
    public function getSticky(): bool
    {
        return $this->sticky;
    }

    /**
     * @param bool $sticky
     *
     * @return $this
     */
    public function setSticky(bool $sticky): static
    {
        $this->sticky = $sticky;

        return $this;
    }


    /**
     * Sticky test.
     * Group: Boolean.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "stickytest"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_boolean"]),
        \ApiPlatform\Metadata\ApiProperty(description: "Sticky test"),
        SymfonySerializer\MaxDepth(2),
        ApiFilter(OrmFilter\OrderFilter::class),
        ApiFilter(OrmFilter\BooleanFilter::class),
        Gedmo\Versioned,
        ORM\Column(
            name: "stickytest",
            type: "boolean",
            nullable: false,
            options: ["default" => false]
        ),
        Serializer\Groups(["nodes_sources", "nodes_sources_boolean"]),
        Serializer\MaxDepth(2),
        Serializer\Type("bool")
    ]
    private bool $stickytest = false;

    /**
     * @return bool
     */
    public function getStickytest(): bool
    {
        return $this->stickytest;
    }

    /**
     * @param bool $stickytest
     *
     * @return $this
     */
    public function setStickytest(bool $stickytest): static
    {
        $this->stickytest = $stickytest;

        return $this;
    }


    /**
     * Custom form.
     *
     * (Virtual field, this var is a buffer)
     */
    #[
        Serializer\Exclude,
        SymfonySerializer\SerializedName(serializedName: "customForm"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default", "nodes_sources_custom_forms"]),
        \ApiPlatform\Metadata\ApiProperty(description: "Custom form"),
        SymfonySerializer\MaxDepth(2)
    ]
    private ?array $customForm = null;

    /**
     * @return \RZ\Roadiz\CoreBundle\Entity\CustomForm[] CustomForm array
     */
    #[
        Serializer\Groups(["nodes_sources", "nodes_sources_default", "nodes_sources_custom_forms"]),
        Serializer\MaxDepth(2),
        Serializer\VirtualProperty,
        Serializer\SerializedName("customForm")
    ]
    public function getCustomForm(): array
    {
        if (null === $this->customForm) {
            if (null !== $this->objectManager) {
                $this->customForm = $this->objectManager
                    ->getRepository(\RZ\Roadiz\CoreBundle\Entity\CustomForm::class)
                    ->findByNodeAndFieldName(
                        $this->getNode(),
                        'custom_form'
                    );
            } else {
                $this->customForm = [];
            }
        }
        return $this->customForm;
    }

    /**
     * @param \RZ\Roadiz\CoreBundle\Entity\CustomForm $customForm
     *
     * @return $this
     */
    public function addCustomForm(\RZ\Roadiz\CoreBundle\Entity\CustomForm $customForm): static
    {
        if (null !== $this->objectManager) {
            $nodeCustomForm = new \RZ\Roadiz\CoreBundle\Entity\NodesCustomForms(
                $this->getNode(),
                $customForm
            );
            $nodeCustomForm->setFieldName('custom_form');
            $this->objectManager->persist($nodeCustomForm);
            $this->getNode()->addCustomForm($nodeCustomForm);
            $this->customForm = null;
        }
        return $this;
    }


    /**
     * Reference to users
     *
     * @var Collection<int, \App\Entity\PositionedPageUser>
     */
    #[
        Serializer\Exclude,
        SymfonySerializer\Ignore,
        ORM\OneToMany(
            targetEntity: \App\Entity\PositionedPageUser::class,
            mappedBy: "nodeSource",
            orphanRemoval: true,
            cascade: ["persist", "remove"]
        ),
        ORM\OrderBy(["position" => "ASC"])
    ]
    private Collection $usersProxy;

    /**
     * @return Collection<int, \App\Entity\PositionedPageUser>
     */
    public function getUsersProxy(): Collection
    {
        return $this->usersProxy;
    }

    /**
     * @return Collection
     */
    #[
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(2),
        Serializer\VirtualProperty,
        Serializer\SerializedName("users"),
        SymfonySerializer\SerializedName(serializedName: "users"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        SymfonySerializer\MaxDepth(2)
    ]
    public function getUsers(): Collection
    {
        return $this->usersProxy->map(function (\App\Entity\PositionedPageUser $proxyEntity) {
            return $proxyEntity->getUser();
        });
    }

    /**
     * @param Collection $usersProxy
     * @Serializer\VirtualProperty()
     * @return $this
     */
    public function setUsersProxy(Collection $usersProxy): static
    {
        $this->usersProxy = $usersProxy;

        return $this;
    }
    /**
     * @param Collection|array|null $users
     * @return $this
     */
    public function setUsers(Collection|array|null $users = null): static
    {
        foreach ($this->getUsersProxy() as $item) {
            $item->setNodeSource(null);
        }
        $this->usersProxy->clear();
        if (null !== $users) {
            $position = 0;
            foreach ($users as $singleUsers) {
                $proxyEntity = new \App\Entity\PositionedPageUser();
                $proxyEntity->setNodeSource($this);
                if ($proxyEntity instanceof \RZ\Roadiz\Core\AbstractEntities\PositionedInterface) {
                    $proxyEntity->setPosition(++$position);
                }
                $proxyEntity->setUser($singleUsers);
                $this->usersProxy->add($proxyEntity);
                $this->objectManager->persist($proxyEntity);
            }
        }

        return $this;
    }


    /**
     * Reference to folders.
     * Default values: # Entity class name
     *     classname: RZ\Roadiz\CoreBundle\Entity\Folder
     *     # Displayable is the method used to display entity name
     *     displayable: getName
     *     # Same as Displayable but for a secondary information
     *     alt_displayable: getFullPath
     *     # Searchable entity fields
     *     searchable:
     *         - folderName
     *     orderBy:
     *         - field: position
     *           direction: ASC
     *     # Use a proxy entity
     *     # proxy:
     *     #     classname: App\Entity\PositionedFolderGalleryBlock
     *     #     self: nodeSource
     *     #     relation: folder
     *     #     # This order will preserve position
     *     #     orderBy:
     *     #         - field: position
     *     #           direction: ASC
     * @var Collection<int, \RZ\Roadiz\CoreBundle\Entity\Folder>
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "folderReferences"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        \ApiPlatform\Metadata\ApiProperty(description: "Reference to folders"),
        SymfonySerializer\MaxDepth(2),
        ORM\ManyToMany(targetEntity: \RZ\Roadiz\CoreBundle\Entity\Folder::class),
        ORM\JoinTable(name: "page_folder_references"),
        ORM\JoinColumn(name: "page_id", referencedColumnName: "id", onDelete: "CASCADE"),
        ORM\InverseJoinColumn(name: "folder_references_id", referencedColumnName: "id", onDelete: "CASCADE"),
        ORM\OrderBy(["position" => "ASC"]),
        ApiFilter(OrmFilter\SearchFilter::class, strategy: "exact"),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(2)
    ]
    private Collection $folderReferences;

    /**
     * @return Collection<int, \RZ\Roadiz\CoreBundle\Entity\Folder>
     */
    public function getFolderReferences(): Collection
    {
        return $this->folderReferences;
    }

    /**
     * @param Collection<int, \RZ\Roadiz\CoreBundle\Entity\Folder>|\RZ\Roadiz\CoreBundle\Entity\Folder[] $folderReferences
     * @return $this
     */
    public function setFolderReferences(Collection|array $folderReferences): static
    {
        if ($folderReferences instanceof \Doctrine\Common\Collections\Collection) {
            $this->folderReferences = $folderReferences;
        } else {
            $this->folderReferences = new \Doctrine\Common\Collections\ArrayCollection($folderReferences);
        }

        return $this;
    }


    /**
     * Amount.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "amount"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        \ApiPlatform\Metadata\ApiProperty(description: "Amount"),
        SymfonySerializer\MaxDepth(2),
        Gedmo\Versioned,
        ORM\Column(
            name: "amount",
            type: "decimal",
            nullable: true,
            precision: 18,
            scale: 3
        ),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(2),
        Serializer\Type("double")
    ]
    private int|float|null $amount = null;

    /**
     * @return int|float|null
     */
    public function getAmount(): int|float|null
    {
        return $this->amount;
    }

    /**
     * @param int|float|null $amount
     *
     * @return $this
     */
    public function setAmount(int|float|null $amount): static
    {
        $this->amount = $amount;

        return $this;
    }


    /**
     * Test email.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "emailTest"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        \ApiPlatform\Metadata\ApiProperty(description: "Test email"),
        SymfonySerializer\MaxDepth(2),
        Gedmo\Versioned,
        ORM\Column(
            name: "email_test",
            type: "string",
            nullable: true,
            length: 250
        ),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(2),
        Serializer\Type("string")
    ]
    private ?string $emailTest = null;

    /**
     * @return string|null
     */
    public function getEmailTest(): ?string
    {
        return $this->emailTest;
    }

    /**
     * @param string|null $emailTest
     *
     * @return $this
     */
    public function setEmailTest(?string $emailTest): static
    {
        $this->emailTest = null !== $emailTest ?
            (string) $emailTest :
            null;

        return $this;
    }


    /**
     * Settings.
     * Default values: classname: Themes\Rozier\Explorer\SettingsProvider
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "settings"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        \ApiPlatform\Metadata\ApiProperty(description: "Settings"),
        SymfonySerializer\MaxDepth(2),
        Gedmo\Versioned,
        ORM\Column(name: "settings", type: "json", nullable: true),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(2)
    ]
    private $settings = null;

    /**
     * @return mixed
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param mixed $settings
     *
     * @return $this
     */
    public function setSettings($settings): static
    {
        $this->settings = $settings;

        return $this;
    }


    /**
     * Folder simple.
     * Default values: classname: Themes\Rozier\Explorer\FoldersProvider
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "folder"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        \ApiPlatform\Metadata\ApiProperty(description: "Folder simple"),
        SymfonySerializer\MaxDepth(2),
        Gedmo\Versioned,
        ORM\Column(
            name: "folder",
            type: "string",
            nullable: true,
            length: 250
        ),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(2)
    ]
    private $folder = null;

    /**
     * @return mixed
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * @param mixed $folder
     *
     * @return $this
     */
    public function setFolder($folder): static
    {
        $this->folder = $folder;

        return $this;
    }


    /**
     * Country.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "country"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        \ApiPlatform\Metadata\ApiProperty(description: "Country"),
        SymfonySerializer\MaxDepth(2),
        Gedmo\Versioned,
        ORM\Column(
            name: "country",
            type: "string",
            nullable: true,
            length: 5
        ),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(2),
        Serializer\Type("string")
    ]
    private ?string $country = null;

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string|null $country
     *
     * @return $this
     */
    public function setCountry(?string $country): static
    {
        $this->country = null !== $country ?
            (string) $country :
            null;

        return $this;
    }


    /**
     * Geolocation.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "geolocation"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        \ApiPlatform\Metadata\ApiProperty(description: "Geolocation"),
        SymfonySerializer\MaxDepth(2),
        Gedmo\Versioned,
        ORM\Column(name: "geolocation", type: "json", nullable: true),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(2)
    ]
    private $geolocation = null;

    /**
     * @return mixed
     */
    public function getGeolocation()
    {
        return $this->geolocation;
    }

    /**
     * @param mixed $geolocation
     *
     * @return $this
     */
    public function setGeolocation($geolocation): static
    {
        $this->geolocation = $geolocation;

        return $this;
    }


    /**
     * Multi geolocations.
     * Group: Geo.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "multiGeolocation"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_geo"]),
        \ApiPlatform\Metadata\ApiProperty(description: "Multi geolocations"),
        SymfonySerializer\MaxDepth(2),
        Gedmo\Versioned,
        ORM\Column(name: "multi_geolocation", type: "json", nullable: true),
        Serializer\Groups(["nodes_sources", "nodes_sources_geo"]),
        Serializer\MaxDepth(2)
    ]
    private $multiGeolocation = null;

    /**
     * @return mixed
     */
    public function getMultiGeolocation()
    {
        return $this->multiGeolocation;
    }

    /**
     * @param mixed $multiGeolocation
     *
     * @return $this
     */
    public function setMultiGeolocation($multiGeolocation): static
    {
        $this->multiGeolocation = $multiGeolocation;

        return $this;
    }


    /**
     * Layout.
     * Default values: dark, transparent
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "layout"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        \ApiPlatform\Metadata\ApiProperty(description: "Layout", schema: ["type" => "string", "enum" => ["dark","transparent"], "example" => "dark"], example: "light"),
        SymfonySerializer\MaxDepth(2),
        ApiFilter(OrmFilter\SearchFilter::class, strategy: "exact"),
        ApiFilter(\RZ\Roadiz\CoreBundle\Api\Filter\NotFilter::class),
        Gedmo\Versioned,
        ORM\Column(
            name: "layout",
            type: "string",
            nullable: true,
            length: 11
        ),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(2),
        Serializer\Type("string")
    ]
    private ?string $layout = null;

    /**
     * @return string|null
     */
    public function getLayout(): ?string
    {
        return $this->layout;
    }

    /**
     * @param string|null $layout
     *
     * @return $this
     */
    public function setLayout(?string $layout): static
    {
        $this->layout = null !== $layout ?
            (string) $layout :
            null;

        return $this;
    }


    /**
     * Main user.
     * Default values: # Entity class name
     *     classname: \RZ\Roadiz\CoreBundle\Entity\User
     *     # Displayable is the method used to display entity name
     *     displayable: getUsername
     *     # Same as Displayable but for a secondary information
     *     alt_displayable: getEmail
     *     # Same as Displayable but for a secondary information
     *     thumbnail: ~
     *     # Searchable entity fields
     *     searchable:
     *         - username
     *         - email
     *     # This order will only be used for explorer
     *     orderBy:
     *         - field: email
     *           direction: ASC
     * @var \RZ\Roadiz\CoreBundle\Entity\User|null
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "mainUser"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        \ApiPlatform\Metadata\ApiProperty(description: "Main user"),
        SymfonySerializer\MaxDepth(2),
        ORM\ManyToOne(targetEntity: \RZ\Roadiz\CoreBundle\Entity\User::class),
        ORM\JoinColumn(name: "main_user_id", referencedColumnName: "id", onDelete: "SET NULL"),
        ApiFilter(OrmFilter\SearchFilter::class, strategy: "exact"),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(2)
    ]
    private ?\RZ\Roadiz\CoreBundle\Entity\User $mainUser = null;

    /**
     * @return \RZ\Roadiz\CoreBundle\Entity\User|null
     */
    public function getMainUser(): ?\RZ\Roadiz\CoreBundle\Entity\User
    {
        return $this->mainUser;
    }

    /**
     * @param \RZ\Roadiz\CoreBundle\Entity\User|null $mainUser
     * @return $this
     */
    public function setMainUser(?\RZ\Roadiz\CoreBundle\Entity\User $mainUser = null): static
    {
        $this->mainUser = $mainUser;

        return $this;
    }


    public function __construct(\RZ\Roadiz\CoreBundle\Entity\Node $node, \RZ\Roadiz\CoreBundle\Entity\Translation $translation)
    {
        parent::__construct($node, $translation);

        $this->usersProxy = new \Doctrine\Common\Collections\ArrayCollection();
        $this->folderReferences = new \Doctrine\Common\Collections\ArrayCollection();
    }

    #[
        Serializer\VirtualProperty,
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\SerializedName("@type"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        SymfonySerializer\SerializedName(serializedName: "@type")
    ]
    public function getNodeTypeName(): string
    {
        return 'Page';
    }

    /**
     * $this->nodeType->isReachable() proxy.
     *
     * @return bool Does this nodeSource is reachable over network?
     */
    public function isReachable(): bool
    {
        return true;
    }

    /**
     * $this->nodeType->isPublishable() proxy.
     *
     * @return bool Does this nodeSource is publishable with date and time?
     */
    public function isPublishable(): bool
    {
        return true;
    }

    public function __clone()
    {
        parent::__clone();

        $usersProxyClone = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($this->usersProxy as $item) {
            $itemClone = clone $item;
            $itemClone->setNodeSource($this);
            $usersProxyClone->add($itemClone);
            $this->objectManager->persist($itemClone);
        }
        $this->usersProxy = $usersProxyClone;
    }

    public function __toString(): string
    {
        return '[NSPage] ' . parent::__toString();
    }
}
