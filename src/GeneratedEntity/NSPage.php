<?php

/**
 * THIS IS A GENERATED FILE, DO NOT EDIT IT.
 * IT WILL BE RECREATED AT EACH NODE-TYPE UPDATE.
 */

declare(strict_types=1);

namespace App\GeneratedEntity;

use ApiPlatform\Doctrine\Orm\Filter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Entity\UserLogEntry;
use Symfony\Component\Serializer\Attribute as Serializer;

/**
 * Page node-source entity.
 * Page
 */
#[Gedmo\Loggable(logEntryClass: UserLogEntry::class)]
#[ORM\Entity(repositoryClass: Repository\NSPageRepository::class)]
#[ORM\Table(name: 'ns_page')]
#[ORM\Index(columns: ['sticky'])]
#[ORM\Index(columns: ['stickytest'])]
#[ORM\Index(columns: ['layout'])]
#[ApiFilter(PropertyFilter::class)]
class NSPage extends NodesSources
{
    /**
     * Sub-title.
     * Sub-title description.
     */
    #[Serializer\SerializedName(serializedName: 'subTitle')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Sub-title: Sub-title description')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'sub_title', type: 'string', nullable: true, length: 250)]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    #[JMS\Type('string')]
    private ?string $subTitle = null;

    /**
     * Content.
     * Content.
     * Default values:
     * allow_h1: false
     * allow_h2: false
     * allow_h3: false
     * allow_h4: false
     * allow_h5: false
     * allow_h6: false
     * allow_list: false
     * allow_blockquote: false
     */
    #[Serializer\SerializedName(serializedName: 'content')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Content: Content')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'content', type: 'text', nullable: true)]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    #[JMS\Type('string')]
    private ?string $content = null;

    /** Page color. */
    #[Serializer\SerializedName(serializedName: 'color')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Page color')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'color', type: 'string', nullable: true, length: 10)]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    #[JMS\Type('string')]
    private ?string $color = null;

    /**
     * Images.
     * (Virtual field, this var is a buffer)
     */
    #[JMS\Exclude]
    #[Serializer\SerializedName(serializedName: 'images')]
    #[Serializer\Groups(['realm_a'])]
    #[ApiProperty(description: 'Images')]
    #[Serializer\MaxDepth(2)]
    private ?array $images = null;

    /**
     * Header image.
     * Group: Images.
     * (Virtual field, this var is a buffer)
     */
    #[JMS\Exclude]
    #[Serializer\SerializedName(serializedName: 'headerImage')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_images', 'nodes_sources_documents'])]
    #[ApiProperty(description: 'Header image')]
    #[Serializer\MaxDepth(2)]
    private ?array $headerImage = null;

    /** Overtitle. */
    #[Serializer\SerializedName(serializedName: 'overTitle')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Overtitle')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'over_title', type: 'string', nullable: true, length: 250)]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    #[JMS\Type('string')]
    private ?string $overTitle = null;

    /**
     * Pictures.
     * Picture for website.
     * Group: Images.
     * (Virtual field, this var is a buffer)
     */
    #[JMS\Exclude]
    #[Serializer\SerializedName(serializedName: 'pictures')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_images', 'nodes_sources_documents'])]
    #[ApiProperty(description: 'Pictures: Picture for website')]
    #[Serializer\MaxDepth(2)]
    private ?array $pictures = null;

    /**
     * nodeReferencesSources NodesSources direct field buffer.
     * @var \RZ\Roadiz\CoreBundle\Entity\NodesSources[]|null
     * References.
     */
    #[JMS\Exclude]
    #[Serializer\SerializedName(serializedName: 'nodeReferences')]
    #[Serializer\Groups(['page_get_by_path'])]
    #[ApiProperty(description: 'References')]
    #[Serializer\MaxDepth(1)]
    #[Serializer\Context(
        normalizationContext: [
        'groups' => ['page_get_by_path', 'urls', 'nodes_sources_base'],
        'skip_null_value' => true,
        'jsonld_embed_context' => false,
        'enable_max_depth' => true,
    ],
        groups: ['page_get_by_path'],
    )]
    private ?array $nodeReferencesSources = null;

    /**
     * Sticky.
     * Group: Boolean.
     */
    #[Serializer\SerializedName(serializedName: 'sticky')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_boolean'])]
    #[ApiProperty(description: 'Sticky')]
    #[Serializer\MaxDepth(2)]
    #[ApiFilter(Filter\OrderFilter::class)]
    #[ApiFilter(Filter\BooleanFilter::class)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'sticky', type: 'boolean', nullable: false, options: ['default' => false])]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_boolean'])]
    #[JMS\MaxDepth(2)]
    #[JMS\Type('bool')]
    private bool $sticky = false;

    /**
     * Sticky test.
     * Group: Boolean.
     */
    #[Serializer\SerializedName(serializedName: 'stickytest')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_boolean'])]
    #[ApiProperty(description: 'Sticky test')]
    #[Serializer\MaxDepth(2)]
    #[ApiFilter(Filter\OrderFilter::class)]
    #[ApiFilter(Filter\BooleanFilter::class)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'stickytest', type: 'boolean', nullable: false, options: ['default' => false])]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_boolean'])]
    #[JMS\MaxDepth(2)]
    #[JMS\Type('bool')]
    private bool $stickytest = false;

    /**
     * Custom form.
     * (Virtual field, this var is a buffer)
     *
     * @var \RZ\Roadiz\CoreBundle\Entity\CustomForm[]|null
     */
    #[JMS\Exclude]
    #[Serializer\SerializedName(serializedName: 'customForm')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default', 'nodes_sources_custom_forms'])]
    #[ApiProperty(description: 'Custom form')]
    #[Serializer\MaxDepth(2)]
    #[Serializer\Context(
        normalizationContext: ['groups' => ['nodes_sources', 'urls']],
        groups: ['nodes_sources', 'nodes_sources_default', 'nodes_sources_custom_forms'],
    )]
    private ?array $customForm = null;

    /**
     * Buffer var to get referenced entities (documents, nodes, cforms, doctrine entities)
     * Reference to users.
     * @var \Doctrine\Common\Collections\Collection<int, \App\Entity\PositionedPageUser>
     */
    #[JMS\Exclude]
    #[Serializer\Ignore]
    #[ORM\OneToMany(
        targetEntity: \App\Entity\PositionedPageUser::class,
        mappedBy: 'nodeSource',
        orphanRemoval: true,
        cascade: ['persist', 'remove'],
    )]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $usersProxy;

    /**
     * Reference to folders.
     * Default values:
     * classname: RZ\Roadiz\CoreBundle\Entity\Folder
     * displayable: getName
     * alt_displayable: getFullPath
     * searchable:
     *     - folderName
     * orderBy:
     *     - { field: position, direction: ASC }
     *
     * @var \Doctrine\Common\Collections\Collection<int, \RZ\Roadiz\CoreBundle\Entity\Folder>
     */
    #[Serializer\SerializedName(serializedName: 'folderReferences')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Reference to folders')]
    #[Serializer\MaxDepth(2)]
    #[ORM\ManyToMany(targetEntity: \RZ\Roadiz\CoreBundle\Entity\Folder::class)]
    #[ORM\JoinTable(name: 'page_folder_references')]
    #[ORM\JoinColumn(name: 'page_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'folder_references_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[ApiFilter(Filter\SearchFilter::class, strategy: 'exact')]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    private Collection $folderReferences;

    /** Amount. */
    #[Serializer\SerializedName(serializedName: 'amount')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Amount')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'amount', type: 'decimal', nullable: true, precision: 18, scale: 3)]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    #[JMS\Type('double')]
    private int|float|null $amount = null;

    /** Test email. */
    #[Serializer\SerializedName(serializedName: 'emailTest')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Test email')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'email_test', type: 'string', nullable: true, length: 250)]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    #[JMS\Type('string')]
    private ?string $emailTest = null;

    /**
     * Settings.
     * Default values:
     * classname: Themes\Rozier\Explorer\SettingsProvider
     */
    #[Serializer\SerializedName(serializedName: 'settings')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Settings')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'settings', type: 'json', nullable: true)]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    private mixed $settings = null;

    /**
     * Folder simple.
     * Default values:
     * classname: Themes\Rozier\Explorer\FoldersProvider
     */
    #[Serializer\SerializedName(serializedName: 'folder')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Folder simple')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'folder', type: 'string', nullable: true, length: 250)]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    private mixed $folder = null;

    /** Country. */
    #[Serializer\SerializedName(serializedName: 'country')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Country')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'country', type: 'string', nullable: true, length: 5)]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    #[JMS\Type('string')]
    private ?string $country = null;

    /** Geolocation. */
    #[Serializer\SerializedName(serializedName: 'geolocation')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Geolocation')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'geolocation', type: 'json', nullable: true)]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    private mixed $geolocation = null;

    /**
     * Multi geolocations.
     * Group: Geo.
     */
    #[Serializer\SerializedName(serializedName: 'multiGeolocation')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_geo'])]
    #[ApiProperty(description: 'Multi geolocations')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'multi_geolocation', type: 'json', nullable: true)]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_geo'])]
    #[JMS\MaxDepth(2)]
    private mixed $multiGeolocation = null;

    /**
     * Layout.
     * Default values:
     * - dark
     * - transparent
     */
    #[Serializer\SerializedName(serializedName: 'layout')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(
        description: 'Layout',
        example: 'light',
        schema: ['type' => 'string', 'enum' => ['dark', 'transparent'], 'example' => 'dark'],
    )]
    #[Serializer\MaxDepth(2)]
    #[ApiFilter(Filter\SearchFilter::class, strategy: 'exact')]
    #[ApiFilter(\RZ\Roadiz\CoreBundle\Api\Filter\NotFilter::class)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'layout', type: 'string', nullable: true, length: 11)]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    #[JMS\Type('string')]
    private ?string $layout = null;

    /**
     * Main user.
     * Default values:
     * classname: \RZ\Roadiz\CoreBundle\Entity\User
     * displayable: getUsername
     * alt_displayable: getEmail
     * thumbnail: null
     * searchable:
     *     - username
     *     - email
     * orderBy:
     *     - { field: email, direction: ASC }
     */
    #[Serializer\SerializedName(serializedName: 'mainUser')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Main user')]
    #[Serializer\MaxDepth(2)]
    #[ORM\ManyToOne(targetEntity: \RZ\Roadiz\CoreBundle\Entity\User::class)]
    #[ORM\JoinColumn(name: 'main_user_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ApiFilter(Filter\SearchFilter::class, strategy: 'exact')]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    private ?\RZ\Roadiz\CoreBundle\Entity\User $mainUser = null;

    /**
     * @return string|null
     */
    public function getSubTitle(): ?string
    {
        return $this->subTitle;
    }

    /**
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
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
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
     * @return string|null
     */
    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
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
     * @return \RZ\Roadiz\CoreBundle\Entity\Document[]
     */
    #[JMS\Groups(['realm_a'])]
    #[JMS\MaxDepth(2)]
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('images')]
    #[JMS\Type('array<RZ\Roadiz\CoreBundle\Entity\Document>')]
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
     * @return $this
     */
    public function addImages(\RZ\Roadiz\CoreBundle\Entity\Document $document): static
    {
        if (null === $this->objectManager) {
            return $this;
        }
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
        return $this;
    }

    /**
     * @return \RZ\Roadiz\CoreBundle\Entity\Document[]
     */
    #[JMS\Groups(['nodes_sources', 'nodes_sources_images', 'nodes_sources_documents'])]
    #[JMS\MaxDepth(2)]
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('headerImage')]
    #[JMS\Type('array<RZ\Roadiz\CoreBundle\Entity\Document>')]
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
     * @return $this
     */
    public function addHeaderImage(\RZ\Roadiz\CoreBundle\Entity\Document $document): static
    {
        if (null === $this->objectManager) {
            return $this;
        }
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
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOverTitle(): ?string
    {
        return $this->overTitle;
    }

    /**
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
     * @return \RZ\Roadiz\CoreBundle\Entity\Document[]
     */
    #[JMS\Groups(['nodes_sources', 'nodes_sources_images', 'nodes_sources_documents'])]
    #[JMS\MaxDepth(2)]
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('pictures')]
    #[JMS\Type('array<RZ\Roadiz\CoreBundle\Entity\Document>')]
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
     * @return $this
     */
    public function addPictures(\RZ\Roadiz\CoreBundle\Entity\Document $document): static
    {
        if (null === $this->objectManager) {
            return $this;
        }
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
        return $this;
    }

    /**
     * @return \RZ\Roadiz\CoreBundle\Entity\NodesSources[]
     */
    #[JMS\Groups(['page_get_by_path'])]
    #[JMS\MaxDepth(1)]
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('nodeReferences')]
    #[JMS\Type('array<RZ\Roadiz\CoreBundle\Entity\NodesSources>')]
    public function getNodeReferencesSources(): array
    {
        if (null === $this->nodeReferencesSources) {
            if (null !== $this->objectManager) {
                $this->nodeReferencesSources = $this->objectManager
                    ->getRepository(\RZ\Roadiz\CoreBundle\Entity\NodesSources::class)
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
     * @param \RZ\Roadiz\CoreBundle\Entity\NodesSources[]|null $nodeReferencesSources
     * @return $this
     */
    public function setNodeReferencesSources(?array $nodeReferencesSources): static
    {
        $this->nodeReferencesSources = $nodeReferencesSources;
        return $this;
    }

    /**
     * @return bool
     */
    public function getSticky(): bool
    {
        return $this->sticky;
    }

    /**
     * @return $this
     */
    public function setSticky(bool $sticky): static
    {
        $this->sticky = $sticky;
        return $this;
    }

    /**
     * @return bool
     */
    public function getStickytest(): bool
    {
        return $this->stickytest;
    }

    /**
     * @return $this
     */
    public function setStickytest(bool $stickytest): static
    {
        $this->stickytest = $stickytest;
        return $this;
    }

    /**
     * @return \RZ\Roadiz\CoreBundle\Entity\CustomForm[] CustomForm array
     */
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default', 'nodes_sources_custom_forms'])]
    #[JMS\MaxDepth(2)]
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('customForm')]
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
     * @return Collection<int, \App\Entity\PositionedPageUser>
     */
    public function getUsersProxy(): Collection
    {
        return $this->usersProxy;
    }

    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('users')]
    #[Serializer\SerializedName(serializedName: 'users')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[Serializer\MaxDepth(2)]
    public function getUsers(): array
    {
        return $this->usersProxy->map(function (\App\Entity\PositionedPageUser $proxyEntity) {
            return $proxyEntity->getUser();
        })->getValues();
    }

    /**
     * @param \Doctrine\Common\Collections\Collection<int, \App\Entity\PositionedPageUser> $usersProxy
     * @return $this
     */
    public function setUsersProxy(Collection $usersProxy): static
    {
        $this->usersProxy = $usersProxy;
        return $this;
    }

    /**
     * @return $this
     */
    public function setUsers(Collection|array|null $users): static
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
     * @return Collection<int, \RZ\Roadiz\CoreBundle\Entity\Folder>
     */
    public function getFolderReferences(): Collection
    {
        return $this->folderReferences;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection<int, \RZ\Roadiz\CoreBundle\Entity\Folder>|array<\RZ\Roadiz\CoreBundle\Entity\Folder> $folderReferences
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
     * @return int|float|null
     */
    public function getAmount(): int|float|null
    {
        return $this->amount;
    }

    /**
     * @return $this
     */
    public function setAmount(int|float|null $amount): static
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmailTest(): ?string
    {
        return $this->emailTest;
    }

    /**
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
     * @return mixed
     */
    public function getSettings(): mixed
    {
        return $this->settings;
    }

    /**
     * @return $this
     */
    public function setSettings(mixed $settings): static
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFolder(): mixed
    {
        return $this->folder;
    }

    /**
     * @return $this
     */
    public function setFolder(mixed $folder): static
    {
        $this->folder = $folder;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
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
     * @return mixed
     */
    public function getGeolocation(): mixed
    {
        return $this->geolocation;
    }

    /**
     * @return $this
     */
    public function setGeolocation(mixed $geolocation): static
    {
        $this->geolocation = $geolocation;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMultiGeolocation(): mixed
    {
        return $this->multiGeolocation;
    }

    /**
     * @return $this
     */
    public function setMultiGeolocation(mixed $multiGeolocation): static
    {
        $this->multiGeolocation = $multiGeolocation;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLayout(): ?string
    {
        return $this->layout;
    }

    /**
     * @return $this
     */
    public function setLayout(?string $layout): static
    {
        $this->layout = null !== $layout ?
                    (string) $layout :
                    null;
        return $this;
    }

    public function getMainUser(): ?\RZ\Roadiz\CoreBundle\Entity\User
    {
        return $this->mainUser;
    }

    /**
     * @return $this
     */
    public function setMainUser(?\RZ\Roadiz\CoreBundle\Entity\User $mainUser): static
    {
        $this->mainUser = $mainUser;
        return $this;
    }

    public function __construct(Node $node, Translation $translation)
    {
        parent::__construct($node, $translation);
        $this->usersProxy = new \Doctrine\Common\Collections\ArrayCollection();
        $this->folderReferences = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __clone(): void
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

    #[JMS\VirtualProperty]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\SerializedName('@type')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[Serializer\SerializedName(serializedName: '@type')]
    public function getNodeTypeName(): string
    {
        return 'Page';
    }

    #[JMS\VirtualProperty]
    #[JMS\Groups(['node_type'])]
    #[JMS\SerializedName('nodeTypeColor')]
    #[Serializer\Groups(['node_type'])]
    #[Serializer\SerializedName(serializedName: 'nodeTypeColor')]
    public function getNodeTypeColor(): string
    {
        return '#000000';
    }

    /**
     * $this->nodeType->isReachable() proxy.
     * @return bool Does this nodeSource is reachable over network?
     */
    #[JMS\VirtualProperty]
    public function isReachable(): bool
    {
        return true;
    }

    /**
     * $this->nodeType->isPublishable() proxy.
     * @return bool Does this nodeSource is publishable with date and time?
     */
    #[JMS\VirtualProperty]
    public function isPublishable(): bool
    {
        return false;
    }

    public function __toString(): string
    {
        return '[NSPage] ' . parent::__toString();
    }
}
