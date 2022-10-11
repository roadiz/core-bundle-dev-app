<?php

declare(strict_types=1);

/*
 * THIS IS A GENERATED FILE, DO NOT EDIT IT
 * IT WILL BE RECREATED AT EACH NODE-TYPE UPDATE
 */
namespace App\GeneratedEntity;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter as OrmFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;

/**
 * DO NOT EDIT
 * Generated custom node-source type by Roadiz.
 */
#[
    Gedmo\Loggable(logEntryClass: \RZ\Roadiz\CoreBundle\Entity\UserLogEntry::class),
    ORM\Entity(repositoryClass: \App\GeneratedEntity\Repository\NSPageRepository::class),
    ORM\Table(name: "ns_page"),
    ORM\Index(columns: ["stickytest"]),
    ORM\Index(columns: ["sticky"]),
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
        SymfonySerializer\MaxDepth(2),
        Gedmo\Versioned,
        ORM\Column(type: "text", nullable: true, name: "content"),
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
    public function setContent($content)
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
        SymfonySerializer\MaxDepth(2),
        Gedmo\Versioned,
        ORM\Column(
            type: "string",
            nullable: true,
            name: "sub_title",
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
    public function setSubTitle($subTitle)
    {
        $this->subTitle = null !== $subTitle ?
            (string) $subTitle :
            null;

        return $this;
    }


    /**
     * Overtitle.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "overTitle"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        SymfonySerializer\MaxDepth(2),
        Gedmo\Versioned,
        ORM\Column(
            type: "string",
            nullable: true,
            name: "over_title",
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
    public function setOverTitle($overTitle)
    {
        $this->overTitle = null !== $overTitle ?
            (string) $overTitle :
            null;

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
            if (
                null !== $this->objectManager &&
                null !== $this->getNode() &&
                null !== $this->getNode()->getNodeType()
            ) {
                $this->headerImage = $this->objectManager
                    ->getRepository(\RZ\Roadiz\CoreBundle\Entity\Document::class)
                    ->findByNodeSourceAndField(
                        $this,
                        $this->getNode()->getNodeType()->getFieldByName("header_image")
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
    public function addHeaderImage(\RZ\Roadiz\CoreBundle\Entity\Document $document)
    {
        if (
            null !== $this->objectManager &&
            null !== $this->getNode() &&
            null !== $this->getNode()->getNodeType()
        ) {
            $field = $this->getNode()->getNodeType()->getFieldByName("header_image");
            if (null !== $field) {
                $nodeSourceDocument = new \RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments(
                    $this,
                    $document,
                    $field
                );
                if (!$this->hasNodesSourcesDocuments($nodeSourceDocument)) {
                    $this->objectManager->persist($nodeSourceDocument);
                    $this->addDocumentsByFields($nodeSourceDocument);
                    $this->headerImage = null;
                }
            }
        }
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
            if (
                null !== $this->objectManager &&
                null !== $this->getNode() &&
                null !== $this->getNode()->getNodeType()
            ) {
                $this->images = $this->objectManager
                    ->getRepository(\RZ\Roadiz\CoreBundle\Entity\Document::class)
                    ->findByNodeSourceAndField(
                        $this,
                        $this->getNode()->getNodeType()->getFieldByName("images")
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
    public function addImages(\RZ\Roadiz\CoreBundle\Entity\Document $document)
    {
        if (
            null !== $this->objectManager &&
            null !== $this->getNode() &&
            null !== $this->getNode()->getNodeType()
        ) {
            $field = $this->getNode()->getNodeType()->getFieldByName("images");
            if (null !== $field) {
                $nodeSourceDocument = new \RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments(
                    $this,
                    $document,
                    $field
                );
                if (!$this->hasNodesSourcesDocuments($nodeSourceDocument)) {
                    $this->objectManager->persist($nodeSourceDocument);
                    $this->addDocumentsByFields($nodeSourceDocument);
                    $this->images = null;
                }
            }
        }
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
            if (
                null !== $this->objectManager &&
                null !== $this->getNode() &&
                null !== $this->getNode()->getNodeType()
            ) {
                $this->pictures = $this->objectManager
                    ->getRepository(\RZ\Roadiz\CoreBundle\Entity\Document::class)
                    ->findByNodeSourceAndField(
                        $this,
                        $this->getNode()->getNodeType()->getFieldByName("pictures")
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
    public function addPictures(\RZ\Roadiz\CoreBundle\Entity\Document $document)
    {
        if (
            null !== $this->objectManager &&
            null !== $this->getNode() &&
            null !== $this->getNode()->getNodeType()
        ) {
            $field = $this->getNode()->getNodeType()->getFieldByName("pictures");
            if (null !== $field) {
                $nodeSourceDocument = new \RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments(
                    $this,
                    $document,
                    $field
                );
                if (!$this->hasNodesSourcesDocuments($nodeSourceDocument)) {
                    $this->objectManager->persist($nodeSourceDocument);
                    $this->addDocumentsByFields($nodeSourceDocument);
                    $this->pictures = null;
                }
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
            if (
                null !== $this->objectManager &&
                null !== $this->getNode() &&
                null !== $this->getNode()->getNodeType()
            ) {
                $this->nodeReferencesSources = $this->objectManager
                    ->getRepository(\App\GeneratedEntity\NSPage::class)
                    ->findByNodesSourcesAndFieldAndTranslation(
                        $this,
                        $this->getNode()->getNodeType()->getFieldByName("node_references")
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
    public function setNodeReferencesSources(?array $nodeReferencesSources)
    {
        $this->nodeReferencesSources = $nodeReferencesSources;

        return $this;
    }


    /**
     * Sticky test.
     * Group: Boolean.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "stickytest"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_boolean"]),
        SymfonySerializer\MaxDepth(2),
        ApiFilter(OrmFilter\OrderFilter::class),
        ApiFilter(OrmFilter\BooleanFilter::class),
        Gedmo\Versioned,
        ORM\Column(
            type: "boolean",
            nullable: false,
            name: "stickytest",
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
    public function setStickytest($stickytest)
    {
        $this->stickytest = $stickytest;

        return $this;
    }


    /**
     * Sticky.
     * Group: Boolean.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "sticky"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_boolean"]),
        SymfonySerializer\MaxDepth(2),
        ApiFilter(OrmFilter\OrderFilter::class),
        ApiFilter(OrmFilter\BooleanFilter::class),
        Gedmo\Versioned,
        ORM\Column(
            type: "boolean",
            nullable: false,
            name: "sticky",
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
    public function setSticky($sticky)
    {
        $this->sticky = $sticky;

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
            if (
                null !== $this->objectManager &&
                null !== $this->getNode() &&
                null !== $this->getNode()->getNodeType()
            ) {
                $this->customForm = $this->objectManager
                    ->getRepository(\RZ\Roadiz\CoreBundle\Entity\CustomForm::class)
                    ->findByNodeAndField(
                        $this->getNode(),
                        $this->getNode()->getNodeType()->getFieldByName("custom_form")
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
    public function addCustomForm(\RZ\Roadiz\CoreBundle\Entity\CustomForm $customForm)
    {
        if (
            null !== $this->objectManager &&
            null !== $this->getNode() &&
            null !== $this->getNode()->getNodeType()
        ) {
            $field = $this->getNode()->getNodeType()->getFieldByName("custom_form");
            if (null !== $field) {
                $nodeCustomForm = new \RZ\Roadiz\CoreBundle\Entity\NodesCustomForms(
                    $this->getNode(),
                    $customForm,
                    $field
                );
                $this->objectManager->persist($nodeCustomForm);
                $this->getNode()->addCustomForm($nodeCustomForm);
                $this->customForm = null;
            }
        }
        return $this;
    }


    /**
     * Reference to users
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\App\Entity\PositionedPageUser>
     */
    #[
        Serializer\Exclude,
        SymfonySerializer\Exclude,
        ORM\OneToMany(
            targetEntity: \App\Entity\PositionedPageUser::class,
            mappedBy: "nodeSource",
            orphanRemoval: true,
            cascade: ["persist", "remove"]
        ),
        ORM\OrderBy(["position" => "ASC"])
    ]
    private $usersProxy;

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getUsersProxy()
    {
        return $this->usersProxy;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
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
    public function getUsers()
    {
        return $this->usersProxy->map(function (\App\Entity\PositionedPageUser $proxyEntity) {
            return $proxyEntity->getUser();
        });
    }

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection $usersProxy
     * @Serializer\VirtualProperty()
     * @return $this
     */
    public function setUsersProxy($usersProxy = null)
    {
        $this->usersProxy = $usersProxy;

        return $this;
    }
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection|null $users
     * @return $this
     */
    public function setUsers($users = null)
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
     * @var \Doctrine\Common\Collections\Collection<RZ\Roadiz\CoreBundle\Entity\Folder>
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "folderReferences"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
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
    private \Doctrine\Common\Collections\Collection $folderReferences;

    /**
     * @return \Doctrine\Common\Collections\Collection<RZ\Roadiz\CoreBundle\Entity\Folder>
     */
    public function getFolderReferences(): \Doctrine\Common\Collections\Collection
    {
        return $this->folderReferences;
    }

    /**
     * @var \Doctrine\Common\Collections\Collection<RZ\Roadiz\CoreBundle\Entity\Folder> $folderReferences
     * @return $this
     */
    public function setFolderReferences($folderReferences)
    {
        if ($folderReferences instanceof \Doctrine\Common\Collections\Collection) {
            $this->folderReferences = $folderReferences;
        } else {
            $this->folderReferences = new \Doctrine\Common\Collections\ArrayCollection($folderReferences);
        }

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

    public function __toString()
    {
        return '[NSPage] ' . parent::__toString();
    }
}
