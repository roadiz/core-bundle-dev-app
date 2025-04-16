<?php

/**
 * THIS IS A GENERATED FILE, DO NOT EDIT IT.
 * IT WILL BE RECREATED AT EACH NODE-TYPE UPDATE.
 */

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Tests\Mocks\GeneratedNodesSources;

use ApiPlatform\Doctrine\Orm\Filter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Entity\UserLogEntry;
use Symfony\Component\Serializer\Attribute as Serializer;
use mock\Entity\NodesSources;

/**
 * Mock node-source entity.
 */
#[Gedmo\Loggable(logEntryClass: UserLogEntry::class)]
#[ORM\Entity(repositoryClass: \mock\Entity\Repository\NodesSourcesRepository::class)]
#[ORM\Table(name: 'ns_mock')]
#[ORM\Index(columns: ['foo_datetime'])]
#[ORM\Index(columns: ['fooIndexed'])]
#[ORM\Index(columns: ['boolIndexed'])]
#[ORM\Index(columns: ['foo_decimal_excluded'])]
#[ORM\Index(columns: ['layout'])]
#[ApiFilter(PropertyFilter::class)]
class NSMock extends NodesSources
{
    /** Foo DateTime field. */
    #[Serializer\SerializedName(serializedName: 'fooDatetime')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default', 'foo_datetime'])]
    #[ApiProperty(description: 'Foo DateTime field')]
    #[Serializer\MaxDepth(2)]
    #[ApiFilter(Filter\OrderFilter::class)]
    #[ApiFilter(Filter\DateFilter::class)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'foo_datetime', type: 'datetime', nullable: true)]
    private ?\DateTime $fooDatetime = null;

    /**
     * Foo field.
     * Maecenas sed diam eget risus varius blandit sit amet non magna.
     */
    #[Serializer\SerializedName(serializedName: 'foo')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Foo field: Maecenas sed diam eget risus varius blandit sit amet non magna')]
    #[Serializer\MaxDepth(1)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'foo', type: 'string', nullable: true, length: 250)]
    private ?string $foo = null;

    /**
     * Foo Multiple field.
     * Default values:
     * - maecenas
     * - eget
     * - risus
     * - varius
     * - blandit
     * - magna
     */
    #[Serializer\SerializedName(serializedName: 'fooMultiple')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Foo Multiple field')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'fooMultiple', type: 'json', nullable: true)]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    private ?array $fooMultiple = null;

    /**
     * Foo indexed field.
     * Maecenas sed diam eget risus varius blandit sit amet non magna.
     */
    #[Serializer\SerializedName(serializedName: 'fooIndexed')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Foo indexed field: Maecenas sed diam eget risus varius blandit sit amet non magna')]
    #[Serializer\MaxDepth(1)]
    #[ApiFilter(Filter\SearchFilter::class, strategy: 'partial')]
    #[ApiFilter(\RZ\Roadiz\CoreBundle\Api\Filter\NotFilter::class)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'fooIndexed', type: 'string', nullable: true, length: 250)]
    private ?string $fooIndexed = null;

    /**
     * Bool indexed field.
     * Maecenas sed diam eget risus varius blandit sit amet non magna.
     */
    #[Serializer\SerializedName(serializedName: 'boolIndexed')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Bool indexed field: Maecenas sed diam eget risus varius blandit sit amet non magna')]
    #[Serializer\MaxDepth(1)]
    #[ApiFilter(Filter\OrderFilter::class)]
    #[ApiFilter(Filter\BooleanFilter::class)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'boolIndexed', type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $boolIndexed = false;

    /**
     * Foo markdown field.
     * Maecenas sed diam eget risus varius blandit sit amet non magna.
     * Default values:
     * allow_h2: false
     * allow_h3: false
     * allow_h4: false
     * allow_h5: false
     * allow_h6: false
     * allow_bold: true
     * allow_italic: true
     * allow_blockquote: false
     * allow_image: false
     * allow_list: false
     * allow_nbsp: true
     * allow_nb_hyphen: true
     * allow_return: true
     * allow_link: false
     * allow_hr: false
     * allow_preview: true
     */
    #[Serializer\SerializedName(serializedName: 'fooMarkdown')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Foo markdown field: Maecenas sed diam eget risus varius blandit sit amet non magna')]
    #[Serializer\MaxDepth(1)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'foo_markdown', type: 'text', nullable: true)]
    private ?string $fooMarkdown = null;

    /**
     * Foo excluded markdown field.
     * Maecenas sed diam eget risus varius blandit sit amet non magna.
     * Default values:
     * allow_h2: false
     * allow_h3: false
     * allow_h4: false
     * allow_h5: false
     * allow_h6: false
     * allow_bold: true
     * allow_italic: true
     * allow_blockquote: false
     * allow_image: false
     * allow_list: false
     * allow_nbsp: true
     * allow_nb_hyphen: true
     * allow_return: true
     * allow_link: false
     * allow_hr: false
     * allow_preview: true
     */
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'foo_markdown_excluded', type: 'text', nullable: true)]
    #[Serializer\Ignore]
    private ?string $fooMarkdownExcluded = null;

    /**
     * Foo expression excluded decimal.
     * Maecenas sed diam eget risus varius blandit sit amet non magna.
     */
    #[Serializer\SerializedName(serializedName: 'fooDecimalExcluded')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Foo expression excluded decimal: Maecenas sed diam eget risus varius blandit sit amet non magna')]
    #[Serializer\MaxDepth(2)]
    #[ApiFilter(Filter\OrderFilter::class)]
    #[ApiFilter(Filter\NumericFilter::class)]
    #[ApiFilter(Filter\RangeFilter::class)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'foo_decimal_excluded', type: 'decimal', nullable: true, precision: 18, scale: 3)]
    private int|float|null $fooDecimalExcluded = null;

    /**
     * Référence à l'événement.
     * Maecenas sed diam eget risus varius blandit sit amet non magna.
     * Default values:
     * # Entity class name
     * classname: \App\Entity\Base\Event
     * # Displayable is the method used to display entity name
     * displayable: getName
     * # Same as Displayable but for a secondary information
     * alt_displayable: getSortingFirstDateTime
     * # Same as Displayable but for a secondary information
     * thumbnail: getMainDocument
     * # Searchable entity fields
     * searchable:
     *     - name
     *     - slug
     * # This order will only be used for explorer
     * orderBy:
     *     - field: sortingLastDateTime
     *       direction: DESC
     */
    #[Serializer\SerializedName(serializedName: 'singleEventReference')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Référence à l\'événement: Maecenas sed diam eget risus varius blandit sit amet non magna')]
    #[Serializer\MaxDepth(2)]
    #[ORM\ManyToOne(targetEntity: \App\Entity\Base\Event::class)]
    #[ORM\JoinColumn(name: 'single_event_reference_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ApiFilter(Filter\SearchFilter::class, strategy: 'exact')]
    private ?\App\Entity\Base\Event $singleEventReference = null;

    /**
     * Remontée d'événements manuelle.
     * Maecenas sed diam eget risus varius blandit sit amet non magna.
     * Default values:
     * # Entity class name
     * classname: \App\Entity\Base\Event
     * # Displayable is the method used to display entity name
     * displayable: getName
     * # Same as Displayable but for a secondary information
     * alt_displayable: getSortingFirstDateTime
     * # Same as Displayable but for a secondary information
     * thumbnail: getMainDocument
     * # Searchable entity fields
     * searchable:
     *     - name
     *     - slug
     * # This order will only be used for explorer
     * orderBy:
     *     - field: sortingLastDateTime
     *       direction: DESC
     * @var \Doctrine\Common\Collections\Collection<int, \App\Entity\Base\Event>
     */
    #[Serializer\SerializedName(serializedName: 'eventReferences')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Remontée d\'événements manuelle: Maecenas sed diam eget risus varius blandit sit amet non magna')]
    #[Serializer\MaxDepth(2)]
    #[ORM\ManyToMany(targetEntity: \App\Entity\Base\Event::class)]
    #[ORM\JoinTable(name: 'node_type_event_references')]
    #[ORM\JoinColumn(name: 'node_type_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'event_references_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\OrderBy(['sortingLastDateTime' => 'DESC'])]
    #[ApiFilter(Filter\SearchFilter::class, strategy: 'exact')]
    private Collection $eventReferences;

    /**
     * Buffer var to get referenced entities (documents, nodes, cforms, doctrine entities)
     * Remontée d'événements manuelle.
     * @var \Doctrine\Common\Collections\Collection<int, \App\Entity\PositionedCity>
     */
    #[Serializer\Ignore]
    #[ORM\OneToMany(
        targetEntity: \App\Entity\PositionedCity::class,
        mappedBy: 'nodeSource',
        orphanRemoval: true,
        cascade: ['persist', 'remove'],
    )]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $eventReferencesProxiedProxy;

    /**
     * Remontée d'événements manuelle exclue.
     * Maecenas sed diam eget risus varius blandit sit amet non magna.
     * Default values:
     * # Entity class name
     * classname: \App\Entity\Base\Event
     * # Displayable is the method used to display entity name
     * displayable: getName
     * # Same as Displayable but for a secondary information
     * alt_displayable: getSortingFirstDateTime
     * # Same as Displayable but for a secondary information
     * thumbnail: getMainDocument
     * # Searchable entity fields
     * searchable:
     *     - name
     *     - slug
     * # This order will only be used for explorer
     * orderBy:
     *     - field: sortingLastDateTime
     *       direction: DESC
     * @var \Doctrine\Common\Collections\Collection<int, \App\Entity\Base\Event>
     */
    #[ORM\ManyToMany(targetEntity: \App\Entity\Base\Event::class)]
    #[ORM\JoinTable(name: 'node_type_event_references_excluded')]
    #[ORM\JoinColumn(name: 'node_type_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'event_references_excluded_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\OrderBy(['sortingLastDateTime' => 'DESC'])]
    #[ApiFilter(Filter\SearchFilter::class, strategy: 'exact')]
    #[Serializer\Ignore]
    private Collection $eventReferencesExcluded;

    /**
     * Bar documents field.
     * Maecenas sed diam eget risus varius blandit sit amet non magna.
     * (Virtual field, this var is a buffer)
     */
    #[Serializer\SerializedName(serializedName: 'bar')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default', 'nodes_sources_documents'])]
    #[ApiProperty(description: 'Bar documents field: Maecenas sed diam eget risus varius blandit sit amet non magna')]
    #[Serializer\MaxDepth(1)]
    private ?array $bar = null;

    /**
     * Custom forms field.
     * (Virtual field, this var is a buffer)
     *
     * @var \mock\Entity\CustomForm[]|null
     */
    #[Serializer\SerializedName(serializedName: 'theForms')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default', 'nodes_sources_custom_forms'])]
    #[ApiProperty(description: 'Custom forms field')]
    #[Serializer\MaxDepth(2)]
    #[Serializer\Context(
        normalizationContext: ['groups' => ['nodes_sources', 'urls']],
        groups: ['nodes_sources', 'nodes_sources_default', 'nodes_sources_custom_forms'],
    )]
    private ?array $theForms = null;

    /**
     * fooBarSources NodesSources direct field buffer.
     * @var \mock\Entity\NodesSources[]|null
     * ForBar nodes field.
     * Maecenas sed diam eget risus varius blandit sit amet non magna.
     */
    #[Serializer\SerializedName(serializedName: 'fooBar')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default', 'nodes_sources_nodes'])]
    #[ApiProperty(description: 'ForBar nodes field: Maecenas sed diam eget risus varius blandit sit amet non magna')]
    #[Serializer\MaxDepth(2)]
    private ?array $fooBarSources = null;

    /**
     * fooBarHiddenSources NodesSources direct field buffer.
     * @var \mock\Entity\NodesSources[]|null
     * ForBar hidden nodes field.
     * Maecenas sed diam eget risus varius blandit sit amet non magna.
     * Default values:
     * - Mock
     * - MockTwo
     */
    private ?array $fooBarHiddenSources = null;

    /**
     * fooBarTypedSources NodesSources direct field buffer.
     * @var \tests\mocks\GeneratedNodesSources\NSMockTwo[]|null
     * ForBar nodes typed field.
     * Default values:
     * - MockTwo
     */
    #[Serializer\SerializedName(serializedName: 'fooBarTyped')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default', 'nodes_sources_nodes'])]
    #[ApiProperty(description: 'ForBar nodes typed field')]
    #[Serializer\MaxDepth(2)]
    private ?array $fooBarTypedSources = null;

    /**
     * ForBar layout enum.
     * Default values:
     * - layout_odd
     * - layout_odd_big_title
     * - layout_even
     * - layout_even_big_title
     * - layout_media_grid
     */
    #[Serializer\SerializedName(serializedName: 'layout')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(
        description: 'ForBar layout enum',
        schema: [
        'type' => 'string',
        'enum' => ['layout_odd', 'layout_odd_big_title', 'layout_even', 'layout_even_big_title', 'layout_media_grid'],
        'example' => 'layout_odd',
    ],
    )]
    #[Serializer\MaxDepth(2)]
    #[ApiFilter(Filter\SearchFilter::class, strategy: 'exact')]
    #[ApiFilter(\RZ\Roadiz\CoreBundle\Api\Filter\NotFilter::class)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'layout', type: 'string', nullable: true, length: 21)]
    private ?string $layout = null;

    /**
     * For many_to_one field.
     * Default values:
     * classname: \MyCustomEntity
     * displayable: getName
     */
    #[Serializer\SerializedName(serializedName: 'fooManyToOne')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'For many_to_one field')]
    #[Serializer\MaxDepth(2)]
    #[ORM\ManyToOne(targetEntity: \MyCustomEntity::class)]
    #[ORM\JoinColumn(name: 'foo_many_to_one_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ApiFilter(Filter\SearchFilter::class, strategy: 'exact')]
    private ?\MyCustomEntity $fooManyToOne = null;

    /**
     * For many_to_many field.
     * Default values:
     * classname: \MyCustomEntity
     * displayable: getName
     * orderBy:
     *     - field: name
     *       direction: asc
     * @var \Doctrine\Common\Collections\Collection<int, \MyCustomEntity>
     */
    #[Serializer\SerializedName(serializedName: 'fooManyToMany')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'For many_to_many field')]
    #[Serializer\MaxDepth(2)]
    #[ORM\ManyToMany(targetEntity: \MyCustomEntity::class)]
    #[ORM\JoinTable(name: 'node_type_foo_many_to_many')]
    #[ORM\JoinColumn(name: 'node_type_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'foo_many_to_many_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\OrderBy(['name' => 'asc'])]
    #[ApiFilter(Filter\SearchFilter::class, strategy: 'exact')]
    private Collection $fooManyToMany;

    /**
     * Buffer var to get referenced entities (documents, nodes, cforms, doctrine entities)
     * For many_to_many proxied field.
     * @var \Doctrine\Common\Collections\Collection<int, \Themes\MyTheme\Entities\PositionedCity>
     */
    #[Serializer\Ignore]
    #[ORM\OneToMany(
        targetEntity: \Themes\MyTheme\Entities\PositionedCity::class,
        mappedBy: 'nodeSource',
        orphanRemoval: true,
        cascade: ['persist', 'remove'],
    )]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $fooManyToManyProxiedProxy;

    /**
     * @return \DateTime|null
     */
    public function getFooDatetime(): ?\DateTime
    {
        return $this->fooDatetime;
    }

    /**
     * @return $this
     */
    public function setFooDatetime(?\DateTime $fooDatetime): static
    {
        $this->fooDatetime = $fooDatetime;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFoo(): ?string
    {
        return $this->foo;
    }

    /**
     * @return $this
     */
    public function setFoo(?string $foo): static
    {
        $this->foo = null !== $foo ?
                    (string) $foo :
                    null;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getFooMultiple(): ?array
    {
        return null !== $this->fooMultiple ? array_values($this->fooMultiple) : null;
    }

    /**
     * @return $this
     */
    public function setFooMultiple(?array $fooMultiple): static
    {
        $this->fooMultiple = (null !== $fooMultiple) ? array_values($fooMultiple) : null;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFooIndexed(): ?string
    {
        return $this->fooIndexed;
    }

    /**
     * @return $this
     */
    public function setFooIndexed(?string $fooIndexed): static
    {
        $this->fooIndexed = null !== $fooIndexed ?
                    (string) $fooIndexed :
                    null;
        return $this;
    }

    /**
     * @return bool
     */
    public function getBoolIndexed(): bool
    {
        return $this->boolIndexed;
    }

    /**
     * @return $this
     */
    public function setBoolIndexed(bool $boolIndexed): static
    {
        $this->boolIndexed = $boolIndexed;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFooMarkdown(): ?string
    {
        return $this->fooMarkdown;
    }

    /**
     * @return $this
     */
    public function setFooMarkdown(?string $fooMarkdown): static
    {
        $this->fooMarkdown = null !== $fooMarkdown ?
                    (string) $fooMarkdown :
                    null;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFooMarkdownExcluded(): ?string
    {
        return $this->fooMarkdownExcluded;
    }

    /**
     * @return $this
     */
    public function setFooMarkdownExcluded(?string $fooMarkdownExcluded): static
    {
        $this->fooMarkdownExcluded = null !== $fooMarkdownExcluded ?
                    (string) $fooMarkdownExcluded :
                    null;
        return $this;
    }

    /**
     * @return int|float|null
     */
    public function getFooDecimalExcluded(): int|float|null
    {
        return $this->fooDecimalExcluded;
    }

    /**
     * @return $this
     */
    public function setFooDecimalExcluded(int|float|null $fooDecimalExcluded): static
    {
        $this->fooDecimalExcluded = $fooDecimalExcluded;
        return $this;
    }

    public function getSingleEventReference(): ?\App\Entity\Base\Event
    {
        return $this->singleEventReference;
    }

    /**
     * @return $this
     */
    public function setSingleEventReference(?\App\Entity\Base\Event $singleEventReference): static
    {
        $this->singleEventReference = $singleEventReference;
        return $this;
    }

    /**
     * @return Collection<int, \App\Entity\Base\Event>
     */
    public function getEventReferences(): Collection
    {
        return $this->eventReferences;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection<int, \App\Entity\Base\Event>|array<\App\Entity\Base\Event> $eventReferences
     * @return $this
     */
    public function setEventReferences(Collection|array $eventReferences): static
    {
        if ($eventReferences instanceof \Doctrine\Common\Collections\Collection) {
            $this->eventReferences = $eventReferences;
        } else {
            $this->eventReferences = new \Doctrine\Common\Collections\ArrayCollection($eventReferences);
        }
        return $this;
    }

    /**
     * @return Collection<int, \App\Entity\PositionedCity>
     */
    public function getEventReferencesProxiedProxy(): Collection
    {
        return $this->eventReferencesProxiedProxy;
    }

    #[Serializer\SerializedName(serializedName: 'eventReferencesProxied')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[Serializer\MaxDepth(2)]
    public function getEventReferencesProxied(): array
    {
        return $this->eventReferencesProxiedProxy->map(function (\App\Entity\PositionedCity $proxyEntity) {
            return $proxyEntity->getCity();
        })->getValues();
    }

    /**
     * @param \Doctrine\Common\Collections\Collection<int, \App\Entity\PositionedCity> $eventReferencesProxiedProxy
     * @return $this
     */
    public function setEventReferencesProxiedProxy(Collection $eventReferencesProxiedProxy): static
    {
        $this->eventReferencesProxiedProxy = $eventReferencesProxiedProxy;
        return $this;
    }

    /**
     * @return $this
     */
    public function setEventReferencesProxied(Collection|array|null $eventReferencesProxied): static
    {
        foreach ($this->getEventReferencesProxiedProxy() as $item) {
            $item->setNodeSource(null);
        }
        $this->eventReferencesProxiedProxy->clear();
        if (null !== $eventReferencesProxied) {
            $position = 0;
            foreach ($eventReferencesProxied as $singleEventReferencesProxied) {
                $proxyEntity = new \App\Entity\PositionedCity();
                $proxyEntity->setNodeSource($this);
                if ($proxyEntity instanceof \RZ\Roadiz\Core\AbstractEntities\PositionedInterface) {
                    $proxyEntity->setPosition(++$position);
                }
                $proxyEntity->setCity($singleEventReferencesProxied);
                $this->eventReferencesProxiedProxy->add($proxyEntity);
                $this->objectManager->persist($proxyEntity);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, \App\Entity\Base\Event>
     */
    public function getEventReferencesExcluded(): Collection
    {
        return $this->eventReferencesExcluded;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection<int, \App\Entity\Base\Event>|array<\App\Entity\Base\Event> $eventReferencesExcluded
     * @return $this
     */
    public function setEventReferencesExcluded(Collection|array $eventReferencesExcluded): static
    {
        if ($eventReferencesExcluded instanceof \Doctrine\Common\Collections\Collection) {
            $this->eventReferencesExcluded = $eventReferencesExcluded;
        } else {
            $this->eventReferencesExcluded = new \Doctrine\Common\Collections\ArrayCollection($eventReferencesExcluded);
        }
        return $this;
    }

    /**
     * @return \mock\Entity\Document[]
     */
    public function getBar(): array
    {
        if (null === $this->bar) {
            if (null !== $this->objectManager) {
                $this->bar = $this->objectManager
                    ->getRepository(\mock\Entity\Document::class)
                    ->findByNodeSourceAndFieldName(
                        $this,
                        'bar'
                    );
            } else {
                $this->bar = [];
            }
        }
        return $this->bar;
    }

    /**
     * @return $this
     */
    public function addBar(\mock\Entity\Document $document): static
    {
        if (null === $this->objectManager) {
            return $this;
        }
        $nodeSourceDocument = new \mock\Entity\NodesSourcesDocument(
            $this,
            $document
        );
        $nodeSourceDocument->setFieldName('bar');
        if (!$this->hasNodesSourcesDocuments($nodeSourceDocument)) {
            $this->objectManager->persist($nodeSourceDocument);
            $this->addDocumentsByFields($nodeSourceDocument);
            $this->bar = null;
        }
        return $this;
    }

    /**
     * @return \mock\Entity\CustomForm[] CustomForm array
     */
    public function getTheForms(): array
    {
        if (null === $this->theForms) {
            if (null !== $this->objectManager) {
                $this->theForms = $this->objectManager
                    ->getRepository(\mock\Entity\CustomForm::class)
                    ->findByNodeAndFieldName(
                        $this->getNode(),
                        'the_forms'
                    );
            } else {
                $this->theForms = [];
            }
        }
        return $this->theForms;
    }

    /**
     * @return $this
     */
    public function addTheForms(\mock\Entity\CustomForm $customForm): static
    {
        if (null !== $this->objectManager) {
            $nodeCustomForm = new \mock\Entity\NodesSourcesCustomForm(
                $this->getNode(),
                $customForm
            );
            $nodeCustomForm->setFieldName('the_forms');
            $this->objectManager->persist($nodeCustomForm);
            $this->getNode()->addCustomForm($nodeCustomForm);
            $this->theForms = null;
        }
        return $this;
    }

    /**
     * @return \mock\Entity\NodesSources[]
     */
    public function getFooBarSources(): array
    {
        if (null === $this->fooBarSources) {
            if (null !== $this->objectManager) {
                $this->fooBarSources = $this->objectManager
                    ->getRepository(\mock\Entity\NodesSources::class)
                    ->findByNodesSourcesAndFieldNameAndTranslation(
                        $this,
                        'foo_bar',
                        []
                    );
            } else {
                $this->fooBarSources = [];
            }
        }
        return $this->fooBarSources;
    }

    /**
     * @param \mock\Entity\NodesSources[]|null $fooBarSources
     * @return $this
     */
    public function setFooBarSources(?array $fooBarSources): static
    {
        $this->fooBarSources = $fooBarSources;
        return $this;
    }

    /**
     * @return \mock\Entity\NodesSources[]
     */
    #[Serializer\Ignore]
    public function getFooBarHiddenSources(): array
    {
        if (null === $this->fooBarHiddenSources) {
            if (null !== $this->objectManager) {
                $this->fooBarHiddenSources = $this->objectManager
                    ->getRepository(\mock\Entity\NodesSources::class)
                    ->findByNodesSourcesAndFieldNameAndTranslation(
                        $this,
                        'foo_bar_hidden',
                        [\tests\mocks\GeneratedNodesSources\NSMock::class, \tests\mocks\GeneratedNodesSources\NSMockTwo::class]
                    );
            } else {
                $this->fooBarHiddenSources = [];
            }
        }
        return $this->fooBarHiddenSources;
    }

    /**
     * @param \mock\Entity\NodesSources[]|null $fooBarHiddenSources
     * @return $this
     */
    public function setFooBarHiddenSources(?array $fooBarHiddenSources): static
    {
        $this->fooBarHiddenSources = $fooBarHiddenSources;
        return $this;
    }

    /**
     * @return \tests\mocks\GeneratedNodesSources\NSMockTwo[]
     */
    public function getFooBarTypedSources(): array
    {
        if (null === $this->fooBarTypedSources) {
            if (null !== $this->objectManager) {
                $this->fooBarTypedSources = $this->objectManager
                    ->getRepository(\tests\mocks\GeneratedNodesSources\NSMockTwo::class)
                    ->findByNodesSourcesAndFieldNameAndTranslation(
                        $this,
                        'foo_bar_typed',
                        []
                    );
            } else {
                $this->fooBarTypedSources = [];
            }
        }
        return $this->fooBarTypedSources;
    }

    /**
     * @param \tests\mocks\GeneratedNodesSources\NSMockTwo[]|null $fooBarTypedSources
     * @return $this
     */
    public function setFooBarTypedSources(?array $fooBarTypedSources): static
    {
        $this->fooBarTypedSources = $fooBarTypedSources;
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

    public function getFooManyToOne(): ?\MyCustomEntity
    {
        return $this->fooManyToOne;
    }

    /**
     * @return $this
     */
    public function setFooManyToOne(?\MyCustomEntity $fooManyToOne): static
    {
        $this->fooManyToOne = $fooManyToOne;
        return $this;
    }

    /**
     * @return Collection<int, \MyCustomEntity>
     */
    public function getFooManyToMany(): Collection
    {
        return $this->fooManyToMany;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection<int, \MyCustomEntity>|array<\MyCustomEntity> $fooManyToMany
     * @return $this
     */
    public function setFooManyToMany(Collection|array $fooManyToMany): static
    {
        if ($fooManyToMany instanceof \Doctrine\Common\Collections\Collection) {
            $this->fooManyToMany = $fooManyToMany;
        } else {
            $this->fooManyToMany = new \Doctrine\Common\Collections\ArrayCollection($fooManyToMany);
        }
        return $this;
    }

    /**
     * @return Collection<int, \Themes\MyTheme\Entities\PositionedCity>
     */
    public function getFooManyToManyProxiedProxy(): Collection
    {
        return $this->fooManyToManyProxiedProxy;
    }

    #[Serializer\SerializedName(serializedName: 'fooManyToManyProxied')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[Serializer\MaxDepth(1)]
    public function getFooManyToManyProxied(): array
    {
        return $this->fooManyToManyProxiedProxy->map(function (\Themes\MyTheme\Entities\PositionedCity $proxyEntity) {
            return $proxyEntity->getCity();
        })->getValues();
    }

    /**
     * @param \Doctrine\Common\Collections\Collection<int, \Themes\MyTheme\Entities\PositionedCity> $fooManyToManyProxiedProxy
     * @return $this
     */
    public function setFooManyToManyProxiedProxy(Collection $fooManyToManyProxiedProxy): static
    {
        $this->fooManyToManyProxiedProxy = $fooManyToManyProxiedProxy;
        return $this;
    }

    /**
     * @return $this
     */
    public function setFooManyToManyProxied(Collection|array|null $fooManyToManyProxied): static
    {
        foreach ($this->getFooManyToManyProxiedProxy() as $item) {
            $item->setNodeSource(null);
        }
        $this->fooManyToManyProxiedProxy->clear();
        if (null !== $fooManyToManyProxied) {
            $position = 0;
            foreach ($fooManyToManyProxied as $singleFooManyToManyProxied) {
                $proxyEntity = new \Themes\MyTheme\Entities\PositionedCity();
                $proxyEntity->setNodeSource($this);
                if ($proxyEntity instanceof \RZ\Roadiz\Core\AbstractEntities\PositionedInterface) {
                    $proxyEntity->setPosition(++$position);
                }
                $proxyEntity->setCity($singleFooManyToManyProxied);
                $this->fooManyToManyProxiedProxy->add($proxyEntity);
                $this->objectManager->persist($proxyEntity);
            }
        }

        return $this;
    }

    public function __construct(\mock\Entity\Node $node, \mock\Entity\Translation $translation)
    {
        parent::__construct($node, $translation);
        $this->eventReferences = new \Doctrine\Common\Collections\ArrayCollection();
        $this->eventReferencesProxiedProxy = new \Doctrine\Common\Collections\ArrayCollection();
        $this->eventReferencesExcluded = new \Doctrine\Common\Collections\ArrayCollection();
        $this->fooManyToMany = new \Doctrine\Common\Collections\ArrayCollection();
        $this->fooManyToManyProxiedProxy = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __clone(): void
    {
        parent::__clone();

        $eventReferencesProxiedProxyClone = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($this->eventReferencesProxiedProxy as $item) {
            $itemClone = clone $item;
            $itemClone->setNodeSource($this);
            $eventReferencesProxiedProxyClone->add($itemClone);
            $this->objectManager->persist($itemClone);
        }
        $this->eventReferencesProxiedProxy = $eventReferencesProxiedProxyClone;

        $fooManyToManyProxiedProxyClone = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($this->fooManyToManyProxiedProxy as $item) {
            $itemClone = clone $item;
            $itemClone->setNodeSource($this);
            $fooManyToManyProxiedProxyClone->add($itemClone);
            $this->objectManager->persist($itemClone);
        }
        $this->fooManyToManyProxiedProxy = $fooManyToManyProxiedProxyClone;
    }

    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[Serializer\SerializedName(serializedName: '@type')]
    public function getNodeTypeName(): string
    {
        return 'Mock';
    }

    #[Serializer\Groups(['node_type'])]
    #[Serializer\SerializedName(serializedName: 'nodeTypeColor')]
    public function getNodeTypeColor(): string
    {
        return '';
    }

    /**
     * $this->nodeType->isReachable() proxy.
     * @return bool Does this nodeSource is reachable over network?
     */
    public function isReachable(): bool
    {
        return true;
    }

    /**
     * $this->nodeType->isPublishable() proxy.
     * @return bool Does this nodeSource is publishable with date and time?
     */
    public function isPublishable(): bool
    {
        return true;
    }

    public function __toString(): string
    {
        return '[NSMock] ' . parent::__toString();
    }
}
