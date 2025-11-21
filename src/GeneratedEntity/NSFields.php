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
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Entity\UserLogEntry;
use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Fields node-source entity.
 * Fields
 */
#[Gedmo\Loggable(logEntryClass: UserLogEntry::class)]
#[ORM\Entity(repositoryClass: Repository\NSFieldsRepository::class)]
#[ORM\Table(name: 'ns_fields')]
#[ORM\Index(columns: ['sticky'])]
#[ORM\Index(columns: ['stickytest'])]
#[ORM\Index(columns: ['price'])]
#[ORM\Index(columns: ['layout'])]
#[ORM\Index(columns: ['datetime'])]
#[ApiFilter(PropertyFilter::class)]
class NSFields extends NodesSources
{
    /**
     * Sub-title.
     * Sub-title description.
     */
    #[Serializer\SerializedName(serializedName: 'subTitle')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Sub-title: Sub-title description')]
    #[Serializer\MaxDepth(2)]
    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'sub_title', type: 'string', nullable: true, length: 250)]
    private ?string $subTitle = null;

    /** Text area. */
    #[Serializer\SerializedName(serializedName: 'longText')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Text area')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'long_text', type: 'text', nullable: true)]
    private ?string $longText = null;

    /**
     * Content.
     * Content.
     * Default values:
     * allow_h1: true
     * allow_h2: true
     * allow_h3: true
     * allow_h4: true
     * allow_h5: true
     * allow_h6: true
     * allow_list: true
     * allow_image: true
     * allow_blockquote: true
     * allow_translate_assistant_translate: true
     * allow_translate_assistant_rephrase: true
     */
    #[Serializer\SerializedName(serializedName: 'content')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Content: Content')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'content', type: 'text', nullable: true)]
    private ?string $content = null;

    /** Page color. */
    #[Serializer\SerializedName(serializedName: 'color')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Page color')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'color', type: 'string', nullable: true, length: 10)]
    private ?string $color = null;

    /**
     * Images.
     * @var \RZ\Roadiz\CoreBundle\Model\DocumentDto[]|null
     * (Virtual field, this var is a buffer)
     */
    #[Serializer\SerializedName(serializedName: 'images')]
    #[Serializer\Groups(['realm_a'])]
    #[ApiProperty(description: 'Images', genId: true)]
    #[Serializer\MaxDepth(2)]
    private ?array $images = null;

    /**
     * nodeReferencesSources NodesSources direct field buffer.
     * @var \RZ\Roadiz\CoreBundle\Entity\NodesSources[]|null
     * References.
     */
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
    private bool $stickytest = false;

    /**
     * Custom form.
     * (Virtual field, this var is a buffer)
     *
     * @var \RZ\Roadiz\CoreBundle\Entity\CustomForm[]|null
     */
    #[Serializer\SerializedName(serializedName: 'customForm')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default', 'nodes_sources_custom_forms'])]
    #[ApiProperty(description: 'Custom form')]
    #[Serializer\MaxDepth(2)]
    #[Serializer\Context(
        normalizationContext: ['groups' => ['nodes_sources', 'urls']],
        groups: ['nodes_sources', 'nodes_sources_default', 'nodes_sources_custom_forms'],
    )]
    private ?array $customForm = null;

    /** Amount. */
    #[Serializer\SerializedName(serializedName: 'amount')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Amount')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'amount', type: 'decimal', nullable: true, precision: 18, scale: 3)]
    private ?string $amount = null;

    /** Price. */
    #[Serializer\SerializedName(serializedName: 'price')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Price')]
    #[Serializer\MaxDepth(2)]
    #[ApiFilter(Filter\OrderFilter::class)]
    #[ApiFilter(Filter\NumericFilter::class)]
    #[ApiFilter(Filter\RangeFilter::class)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'price', type: 'integer', nullable: true)]
    private ?int $price = null;

    /** Test email. */
    #[Serializer\SerializedName(serializedName: 'emailTest')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Test email')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'email_test', type: 'string', nullable: true, length: 250)]
    private ?string $emailTest = null;

    /**
     * Settings.
     * Default values:
     * classname: RZ\Roadiz\RozierBundle\Explorer\Provider\SettingsProvider
     */
    #[Serializer\SerializedName(serializedName: 'settings')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Settings')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'settings', type: 'json', nullable: true)]
    private mixed $settings = null;

    /**
     * Contacts.
     * Default values:
     * entry_type: App\Form\ContactFormType
     */
    #[Serializer\SerializedName(serializedName: 'contacts')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Contacts')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'contacts', type: 'json', nullable: true)]
    private mixed $contacts = null;

    /**
     * Folder simple.
     * Default values:
     * classname: RZ\Roadiz\RozierBundle\Explorer\Provider\FoldersProvider
     */
    #[Serializer\SerializedName(serializedName: 'folder')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Folder simple')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'folder', type: 'string', nullable: true, length: 250)]
    private mixed $folder = null;

    /** Country. */
    #[Serializer\SerializedName(serializedName: 'country')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Country')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'country', type: 'string', nullable: true, length: 5)]
    private ?string $country = null;

    /** Geolocation. */
    #[Serializer\SerializedName(serializedName: 'geolocation')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Geolocation')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'geolocation', type: 'json', nullable: true)]
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
    #[Assert\NotBlank]
    #[ORM\ManyToOne(targetEntity: \RZ\Roadiz\CoreBundle\Entity\User::class)]
    #[ORM\JoinColumn(name: 'main_user_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ApiFilter(Filter\SearchFilter::class, strategy: 'exact')]
    private ?\RZ\Roadiz\CoreBundle\Entity\User $mainUser = null;

    /** Date. */
    #[Serializer\SerializedName(serializedName: 'date')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Date')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'date', type: 'datetime', nullable: true)]
    private ?\DateTime $date = null;

    /** Date time. */
    #[Serializer\SerializedName(serializedName: 'datetime')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Date time')]
    #[Serializer\MaxDepth(2)]
    #[ApiFilter(Filter\OrderFilter::class)]
    #[ApiFilter(Filter\DateFilter::class)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'datetime', type: 'datetime', nullable: true)]
    private ?\DateTime $datetime = null;

    /** Css. */
    #[Serializer\SerializedName(serializedName: 'css')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Css')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'css', type: 'text', nullable: true)]
    private mixed $css = null;

    /** Yaml. */
    #[Serializer\SerializedName(serializedName: 'yaml')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default', 'nodes_sources_yaml'])]
    #[ApiProperty(description: 'Yaml')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'yaml', type: 'text', nullable: true)]
    private mixed $yaml = null;

    /** Json. */
    #[Serializer\SerializedName(serializedName: 'json')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Json')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'json', type: 'text', nullable: true)]
    private mixed $json = null;

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
    public function getLongText(): ?string
    {
        return $this->longText;
    }

    /**
     * @return $this
     */
    public function setLongText(?string $longText): static
    {
        $this->longText = null !== $longText ?
                    (string) $longText :
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
     * @return \RZ\Roadiz\CoreBundle\Model\DocumentDto[]
     */
    public function getImages(): array
    {
        if (null === $this->images) {
            if (null !== $this->objectManager) {
                $this->images = $this->objectManager
                    ->getRepository(\RZ\Roadiz\CoreBundle\Entity\Document::class)
                    ->findDocumentDtoByNodeSourceAndFieldName(
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
     * @return \RZ\Roadiz\CoreBundle\Entity\NodesSources[]
     */
    public function getNodeReferencesSources(): array
    {
        if (null === $this->nodeReferencesSources) {
            if (null !== $this->objectManager) {
                $this->nodeReferencesSources = $this->objectManager
                    ->getRepository(\RZ\Roadiz\CoreBundle\Entity\NodesSources::class)
                    ->findByNodesSourcesAndFieldNameAndTranslation(
                        $this,
                        'node_references',
                        []
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
     * @return string|null
     */
    public function getAmount(): ?string
    {
        return $this->amount;
    }

    /**
     * @return $this
     */
    public function setAmount(string|int|float|null $amount): static
    {
        $this->amount = null !== $amount ?
                    (string) $amount :
                    null;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPrice(): ?int
    {
        return $this->price;
    }

    /**
     * @return $this
     */
    public function setPrice(?int $price): static
    {
        $this->price = null !== $price ?
                    (int) $price :
                    null;
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
    public function getContacts(): mixed
    {
        return $this->contacts;
    }

    /**
     * @return $this
     */
    public function setContacts(mixed $contacts): static
    {
        $this->contacts = $contacts;
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

    /**
     * @return \DateTime|null
     */
    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    /**
     * @return $this
     */
    public function setDate(?\DateTime $date): static
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDatetime(): ?\DateTime
    {
        return $this->datetime;
    }

    /**
     * @return $this
     */
    public function setDatetime(?\DateTime $datetime): static
    {
        $this->datetime = $datetime;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCss(): mixed
    {
        return $this->css;
    }

    /**
     * @return $this
     */
    public function setCss(mixed $css): static
    {
        $this->css = $css;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getYaml(): mixed
    {
        return $this->yaml;
    }

    #[Serializer\SerializedName(serializedName: 'yaml')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default', 'nodes_sources_yaml'])]
    #[Serializer\MaxDepth(2)]
    public function getYamlAsObject(): object|array|null
    {
        if (null !== $this->yaml) {
            return \Symfony\Component\Yaml\Yaml::parse($this->yaml);
        }
        return null;
    }

    /**
     * @return $this
     */
    public function setYaml(mixed $yaml): static
    {
        $this->yaml = $yaml;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getJson(): mixed
    {
        return $this->json;
    }

    /**
     * @return $this
     */
    public function setJson(mixed $json): static
    {
        $this->json = $json;
        return $this;
    }

    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[Serializer\SerializedName(serializedName: '@type')]
    #[\Override]
    public function getNodeTypeName(): string
    {
        return 'Fields';
    }

    #[Serializer\Groups(['node_type'])]
    #[Serializer\SerializedName(serializedName: 'nodeTypeColor')]
    #[\Override]
    public function getNodeTypeColor(): string
    {
        return '#00FF00';
    }

    /**
     * $this->nodeType->isReachable() proxy.
     * @return bool Does this nodeSource is reachable over network?
     */
    #[\Override]
    public function isReachable(): bool
    {
        return true;
    }

    /**
     * $this->nodeType->isPublishable() proxy.
     * @return bool Does this nodeSource is publishable with date and time?
     */
    #[\Override]
    public function isPublishable(): bool
    {
        return false;
    }

    #[\Override]
    public function __toString(): string
    {
        return '[NSFields] ' . parent::__toString();
    }
}
