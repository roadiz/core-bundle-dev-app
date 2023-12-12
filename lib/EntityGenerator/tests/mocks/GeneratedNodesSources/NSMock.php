<?php

declare(strict_types=1);

/*
 * THIS IS A GENERATED FILE, DO NOT EDIT IT
 * IT WILL BE RECREATED AT EACH NODE-TYPE UPDATE
 */
namespace tests\mocks\GeneratedNodesSources;

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
    ORM\Entity(repositoryClass: \mock\Entity\Repository\NodesSourcesRepository::class),
    ORM\Table(name: "ns_mock"),
    ORM\Index(columns: ["foo_datetime"]),
    ORM\Index(columns: ["fooIndexed"]),
    ORM\Index(columns: ["boolIndexed"]),
    ORM\Index(columns: ["foo_decimal_excluded"]),
    ORM\Index(columns: ["layout"]),
    ApiFilter(PropertyFilter::class)
]
class NSMock extends \mock\Entity\NodesSources
{
    /**
     * Foo DateTime field.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "fooDatetime"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default", "foo_datetime"]),
        SymfonySerializer\MaxDepth(2),
        ApiFilter(OrmFilter\OrderFilter::class),
        ApiFilter(OrmFilter\DateFilter::class),
        Gedmo\Versioned,
        ORM\Column(name: "foo_datetime", type: "datetime", nullable: true),
        Serializer\Groups(["nodes_sources", "nodes_sources_default", "foo_datetime"]),
        Serializer\MaxDepth(2),
        Serializer\Type("DateTime")
    ]
    private ?\DateTime $fooDatetime = null;

    /**
     * @return \DateTime|null
     */
    public function getFooDatetime(): ?\DateTime
    {
        return $this->fooDatetime;
    }

    /**
     * @param \DateTime|null $fooDatetime
     *
     * @return $this
     */
    public function setFooDatetime(?\DateTime $fooDatetime): static
    {
        $this->fooDatetime = $fooDatetime;

        return $this;
    }


    /**
     * Foo field.
     * Maecenas sed diam eget risus varius blandit sit amet non magna.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "foo"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        SymfonySerializer\MaxDepth(1),
        Gedmo\Versioned,
        ORM\Column(
            name: "foo",
            type: "string",
            nullable: true,
            length: 250
        ),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(1),
        Serializer\Type("string")
    ]
    private ?string $foo = null;

    /**
     * @return string|null
     */
    public function getFoo(): ?string
    {
        return $this->foo;
    }

    /**
     * @param string|null $foo
     *
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
     * Foo indexed field.
     * Maecenas sed diam eget risus varius blandit sit amet non magna.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "fooIndexed"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        SymfonySerializer\MaxDepth(1),
        ApiFilter(OrmFilter\SearchFilter::class, strategy: "partial"),
        ApiFilter(\RZ\Roadiz\CoreBundle\Api\Filter\NotFilter::class),
        Gedmo\Versioned,
        ORM\Column(
            name: "fooIndexed",
            type: "string",
            nullable: true,
            length: 250
        ),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(1),
        Serializer\Type("string")
    ]
    private ?string $fooIndexed = null;

    /**
     * @return string|null
     */
    public function getFooIndexed(): ?string
    {
        return $this->fooIndexed;
    }

    /**
     * @param string|null $fooIndexed
     *
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
     * Bool indexed field.
     * Maecenas sed diam eget risus varius blandit sit amet non magna.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "boolIndexed"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        SymfonySerializer\MaxDepth(1),
        ApiFilter(OrmFilter\OrderFilter::class),
        ApiFilter(OrmFilter\BooleanFilter::class),
        Gedmo\Versioned,
        ORM\Column(
            name: "boolIndexed",
            type: "boolean",
            nullable: false,
            options: ["default" => false]
        ),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(1),
        Serializer\Type("bool")
    ]
    private bool $boolIndexed = false;

    /**
     * @return bool
     */
    public function getBoolIndexed(): bool
    {
        return $this->boolIndexed;
    }

    /**
     * @param bool $boolIndexed
     *
     * @return $this
     */
    public function setBoolIndexed(bool $boolIndexed): static
    {
        $this->boolIndexed = $boolIndexed;

        return $this;
    }


    /**
     * Foo markdown field.
     * Maecenas sed diam eget risus varius blandit sit amet non magna.
     * Default values: allow_h2: false
     *     allow_h3: false
     *     allow_h4: false
     *     allow_h5: false
     *     allow_h6: false
     *     allow_bold: true
     *     allow_italic: true
     *     allow_blockquote: false
     *     allow_image: false
     *     allow_list: false
     *     allow_nbsp: true
     *     allow_nb_hyphen: true
     *     allow_return: true
     *     allow_link: false
     *     allow_hr: false
     *     allow_preview: true
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "fooMarkdown"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        SymfonySerializer\MaxDepth(1),
        Gedmo\Versioned,
        ORM\Column(name: "foo_markdown", type: "text", nullable: true),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(1),
        Serializer\Type("string")
    ]
    private ?string $fooMarkdown = null;

    /**
     * @return string|null
     */
    public function getFooMarkdown(): ?string
    {
        return $this->fooMarkdown;
    }

    /**
     * @param string|null $fooMarkdown
     *
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
     * Foo excluded markdown field.
     * Maecenas sed diam eget risus varius blandit sit amet non magna.
     * Default values: allow_h2: false
     *     allow_h3: false
     *     allow_h4: false
     *     allow_h5: false
     *     allow_h6: false
     *     allow_bold: true
     *     allow_italic: true
     *     allow_blockquote: false
     *     allow_image: false
     *     allow_list: false
     *     allow_nbsp: true
     *     allow_nb_hyphen: true
     *     allow_return: true
     *     allow_link: false
     *     allow_hr: false
     *     allow_preview: true
     */
    #[
        Gedmo\Versioned,
        ORM\Column(name: "foo_markdown_excluded", type: "text", nullable: true),
        Serializer\Exclude,
        SymfonySerializer\Ignore
    ]
    private ?string $fooMarkdownExcluded = null;

    /**
     * @return string|null
     */
    public function getFooMarkdownExcluded(): ?string
    {
        return $this->fooMarkdownExcluded;
    }

    /**
     * @param string|null $fooMarkdownExcluded
     *
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
     * Foo expression excluded decimal.
     * Maecenas sed diam eget risus varius blandit sit amet non magna.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "fooDecimalExcluded"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        SymfonySerializer\MaxDepth(2),
        ApiFilter(OrmFilter\OrderFilter::class),
        ApiFilter(OrmFilter\NumericFilter::class),
        ApiFilter(OrmFilter\RangeFilter::class),
        Gedmo\Versioned,
        ORM\Column(
            name: "foo_decimal_excluded",
            type: "decimal",
            nullable: true,
            precision: 18,
            scale: 3
        ),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(2),
        Serializer\Exclude(if: "object.foo == 'test'"),
        Serializer\Type("double")
    ]
    private int|float|null $fooDecimalExcluded = null;

    /**
     * @return int|float|null
     */
    public function getFooDecimalExcluded(): int|float|null
    {
        return $this->fooDecimalExcluded;
    }

    /**
     * @param int|float|null $fooDecimalExcluded
     *
     * @return $this
     */
    public function setFooDecimalExcluded(int|float|null $fooDecimalExcluded): static
    {
        $this->fooDecimalExcluded = $fooDecimalExcluded;

        return $this;
    }


    /**
     * Référence à l'événement.
     * Maecenas sed diam eget risus varius blandit sit amet non magna.
     * Default values: # Entity class name
     *     classname: \App\Entity\Base\Event
     *     # Displayable is the method used to display entity name
     *     displayable: getName
     *     # Same as Displayable but for a secondary information
     *     alt_displayable: getSortingFirstDateTime
     *     # Same as Displayable but for a secondary information
     *     thumbnail: getMainDocument
     *     # Searchable entity fields
     *     searchable:
     *         - name
     *         - slug
     *     # This order will only be used for explorer
     *     orderBy:
     *         - field: sortingLastDateTime
     *           direction: DESC
     * @var \App\Entity\Base\Event|null
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "singleEventReference"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        SymfonySerializer\MaxDepth(2),
        ORM\ManyToOne(targetEntity: \App\Entity\Base\Event::class),
        ORM\JoinColumn(name: "single_event_reference_id", referencedColumnName: "id", onDelete: "SET NULL"),
        ApiFilter(OrmFilter\SearchFilter::class, strategy: "exact"),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(2)
    ]
    private ?\App\Entity\Base\Event $singleEventReference = null;

    /**
     * @return \App\Entity\Base\Event|null
     */
    public function getSingleEventReference(): ?\App\Entity\Base\Event
    {
        return $this->singleEventReference;
    }

    /**
     * @param \App\Entity\Base\Event|null $singleEventReference
     * @return $this
     */
    public function setSingleEventReference(?\App\Entity\Base\Event $singleEventReference = null): static
    {
        $this->singleEventReference = $singleEventReference;

        return $this;
    }


    /**
     * Remontée d'événements manuelle.
     * Maecenas sed diam eget risus varius blandit sit amet non magna.
     * Default values: # Entity class name
     *     classname: \App\Entity\Base\Event
     *     # Displayable is the method used to display entity name
     *     displayable: getName
     *     # Same as Displayable but for a secondary information
     *     alt_displayable: getSortingFirstDateTime
     *     # Same as Displayable but for a secondary information
     *     thumbnail: getMainDocument
     *     # Searchable entity fields
     *     searchable:
     *         - name
     *         - slug
     *     # This order will only be used for explorer
     *     orderBy:
     *         - field: sortingLastDateTime
     *           direction: DESC
     * @var Collection<int, \App\Entity\Base\Event>
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "eventReferences"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        SymfonySerializer\MaxDepth(2),
        ORM\ManyToMany(targetEntity: \App\Entity\Base\Event::class),
        ORM\JoinTable(name: "node_type_event_references"),
        ORM\JoinColumn(name: "node_type_id", referencedColumnName: "id", onDelete: "CASCADE"),
        ORM\InverseJoinColumn(name: "event_references_id", referencedColumnName: "id", onDelete: "CASCADE"),
        ORM\OrderBy(["sortingLastDateTime" => "DESC"]),
        ApiFilter(OrmFilter\SearchFilter::class, strategy: "exact"),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(2)
    ]
    private Collection $eventReferences;

    /**
     * @return Collection<int, \App\Entity\Base\Event>
     */
    public function getEventReferences(): Collection
    {
        return $this->eventReferences;
    }

    /**
     * @param Collection<int, \App\Entity\Base\Event>|\App\Entity\Base\Event[] $eventReferences
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
     * Remontée d'événements manuelle
     *
     * @var Collection<int, \App\Entity\PositionedCity>
     */
    #[
        Serializer\Exclude,
        SymfonySerializer\Ignore,
        ORM\OneToMany(
            targetEntity: \App\Entity\PositionedCity::class,
            mappedBy: "nodeSource",
            orphanRemoval: true,
            cascade: ["persist", "remove"]
        ),
        ORM\OrderBy(["position" => "ASC"])
    ]
    private Collection $eventReferencesProxiedProxy;

    /**
     * @return Collection<int, \App\Entity\PositionedCity>
     */
    public function getEventReferencesProxiedProxy(): Collection
    {
        return $this->eventReferencesProxiedProxy;
    }

    /**
     * @return Collection
     */
    #[
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(2),
        Serializer\VirtualProperty,
        Serializer\SerializedName("eventReferencesProxied"),
        SymfonySerializer\SerializedName(serializedName: "eventReferencesProxied"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        SymfonySerializer\MaxDepth(2)
    ]
    public function getEventReferencesProxied(): Collection
    {
        return $this->eventReferencesProxiedProxy->map(function (\App\Entity\PositionedCity $proxyEntity) {
            return $proxyEntity->getCity();
        });
    }

    /**
     * @param Collection $eventReferencesProxiedProxy
     * @Serializer\VirtualProperty()
     * @return $this
     */
    public function setEventReferencesProxiedProxy(Collection $eventReferencesProxiedProxy): static
    {
        $this->eventReferencesProxiedProxy = $eventReferencesProxiedProxy;

        return $this;
    }
    /**
     * @param Collection|array|null $eventReferencesProxied
     * @return $this
     */
    public function setEventReferencesProxied(Collection|array|null $eventReferencesProxied = null): static
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
     * Remontée d'événements manuelle exclue.
     * Maecenas sed diam eget risus varius blandit sit amet non magna.
     * Default values: # Entity class name
     *     classname: \App\Entity\Base\Event
     *     # Displayable is the method used to display entity name
     *     displayable: getName
     *     # Same as Displayable but for a secondary information
     *     alt_displayable: getSortingFirstDateTime
     *     # Same as Displayable but for a secondary information
     *     thumbnail: getMainDocument
     *     # Searchable entity fields
     *     searchable:
     *         - name
     *         - slug
     *     # This order will only be used for explorer
     *     orderBy:
     *         - field: sortingLastDateTime
     *           direction: DESC
     * @var Collection<int, \App\Entity\Base\Event>
     */
    #[
        ORM\ManyToMany(targetEntity: \App\Entity\Base\Event::class),
        ORM\JoinTable(name: "node_type_event_references_excluded"),
        ORM\JoinColumn(name: "node_type_id", referencedColumnName: "id", onDelete: "CASCADE"),
        ORM\InverseJoinColumn(name: "event_references_excluded_id", referencedColumnName: "id", onDelete: "CASCADE"),
        ORM\OrderBy(["sortingLastDateTime" => "DESC"]),
        ApiFilter(OrmFilter\SearchFilter::class, strategy: "exact"),
        Serializer\Exclude,
        SymfonySerializer\Ignore
    ]
    private Collection $eventReferencesExcluded;

    /**
     * @return Collection<int, \App\Entity\Base\Event>
     */
    public function getEventReferencesExcluded(): Collection
    {
        return $this->eventReferencesExcluded;
    }

    /**
     * @param Collection<int, \App\Entity\Base\Event>|\App\Entity\Base\Event[] $eventReferencesExcluded
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
     * Bar documents field.
     * Maecenas sed diam eget risus varius blandit sit amet non magna.
     *
     * (Virtual field, this var is a buffer)
     */
    #[
        Serializer\Exclude,
        SymfonySerializer\SerializedName(serializedName: "bar"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default", "nodes_sources_documents"]),
        SymfonySerializer\MaxDepth(1)
    ]
    private ?array $bar = null;

    /**
     * @return \mock\Entity\Document[] Documents array
     */
    #[
        Serializer\Groups(["nodes_sources", "nodes_sources_default", "nodes_sources_documents"]),
        Serializer\MaxDepth(1),
        Serializer\VirtualProperty,
        Serializer\SerializedName("bar"),
        Serializer\Type("array<mock\Entity\Document>")
    ]
    public function getBar(): array
    {
        if (null === $this->bar) {
            if (
                null !== $this->objectManager &&
                null !== $this->getNode() &&
                null !== $this->getNode()->getNodeType()
            ) {
                $this->bar = $this->objectManager
                    ->getRepository(\mock\Entity\Document::class)
                    ->findByNodeSourceAndField(
                        $this,
                        $this->getNode()->getNodeType()->getFieldByName("bar")
                    );
            } else {
                $this->bar = [];
            }
        }
        return $this->bar;
    }

    /**
     * @param \mock\Entity\Document $document
     *
     * @return $this
     */
    public function addBar(\mock\Entity\Document $document): static
    {
        if (
            null !== $this->objectManager &&
            null !== $this->getNode() &&
            null !== $this->getNode()->getNodeType()
        ) {
            $field = $this->getNode()->getNodeType()->getFieldByName("bar");
            if (null !== $field) {
                $nodeSourceDocument = new \mock\Entity\NodesSourcesDocument(
                    $this,
                    $document,
                    $field
                );
                if (!$this->hasNodesSourcesDocuments($nodeSourceDocument)) {
                    $this->objectManager->persist($nodeSourceDocument);
                    $this->addDocumentsByFields($nodeSourceDocument);
                    $this->bar = null;
                }
            }
        }
        return $this;
    }


    /**
     * Custom forms field.
     *
     * (Virtual field, this var is a buffer)
     */
    #[
        Serializer\Exclude,
        SymfonySerializer\SerializedName(serializedName: "theForms"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default", "nodes_sources_custom_forms"]),
        SymfonySerializer\MaxDepth(2)
    ]
    private ?array $theForms = null;

    /**
     * @return \mock\Entity\CustomForm[] CustomForm array
     */
    #[
        Serializer\Groups(["nodes_sources", "nodes_sources_default", "nodes_sources_custom_forms"]),
        Serializer\MaxDepth(2),
        Serializer\VirtualProperty,
        Serializer\SerializedName("theForms")
    ]
    public function getTheForms(): array
    {
        if (null === $this->theForms) {
            if (
                null !== $this->objectManager &&
                null !== $this->getNode() &&
                null !== $this->getNode()->getNodeType()
            ) {
                $this->theForms = $this->objectManager
                    ->getRepository(\mock\Entity\CustomForm::class)
                    ->findByNodeAndField(
                        $this->getNode(),
                        $this->getNode()->getNodeType()->getFieldByName("the_forms")
                    );
            } else {
                $this->theForms = [];
            }
        }
        return $this->theForms;
    }

    /**
     * @param \mock\Entity\CustomForm $customForm
     *
     * @return $this
     */
    public function addTheForms(\mock\Entity\CustomForm $customForm): static
    {
        if (
            null !== $this->objectManager &&
            null !== $this->getNode() &&
            null !== $this->getNode()->getNodeType()
        ) {
            $field = $this->getNode()->getNodeType()->getFieldByName("the_forms");
            if (null !== $field) {
                $nodeCustomForm = new \mock\Entity\NodesSourcesCustomForm(
                    $this->getNode(),
                    $customForm,
                    $field
                );
                $this->objectManager->persist($nodeCustomForm);
                $this->getNode()->addCustomForm($nodeCustomForm);
                $this->theForms = null;
            }
        }
        return $this;
    }


    /**
     * fooBarSources NodesSources direct field buffer.
     * (Virtual field, this var is a buffer)
     *
     * ForBar nodes field.
     * Maecenas sed diam eget risus varius blandit sit amet non magna.
     * @var \mock\Entity\NodesSources[]|null
     */
    #[
        Serializer\Exclude,
        SymfonySerializer\SerializedName(serializedName: "fooBar"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default", "nodes_sources_nodes"]),
        SymfonySerializer\MaxDepth(2)
    ]
    private ?array $fooBarSources = null;

    /**
     * @return \mock\Entity\NodesSources[] fooBar nodes-sources array
     */
    #[
        Serializer\Groups(["nodes_sources", "nodes_sources_default", "nodes_sources_nodes"]),
        Serializer\MaxDepth(2),
        Serializer\VirtualProperty,
        Serializer\SerializedName("fooBar"),
        Serializer\Type("array<mock\Entity\NodesSources>")
    ]
    public function getFooBarSources(): array
    {
        if (null === $this->fooBarSources) {
            if (
                null !== $this->objectManager &&
                null !== $this->getNode() &&
                null !== $this->getNode()->getNodeType()
            ) {
                $this->fooBarSources = $this->objectManager
                    ->getRepository(\mock\Entity\NodesSources::class)
                    ->findByNodesSourcesAndFieldAndTranslation(
                        $this,
                        $this->getNode()->getNodeType()->getFieldByName("foo_bar")
                    );
            } else {
                $this->fooBarSources = [];
            }
        }
        return $this->fooBarSources;
    }

    /**
     * @param \mock\Entity\NodesSources[]|null $fooBarSources
     *
     * @return $this
     */
    public function setFooBarSources(?array $fooBarSources): static
    {
        $this->fooBarSources = $fooBarSources;

        return $this;
    }


    /**
     * fooBarHiddenSources NodesSources direct field buffer.
     * (Virtual field, this var is a buffer)
     *
     * ForBar hidden nodes field.
     * Maecenas sed diam eget risus varius blandit sit amet non magna.
     * @var \mock\Entity\NodesSources[]|null
     */
    #[Serializer\Exclude]
    private ?array $fooBarHiddenSources = null;

    /**
     * @return \mock\Entity\NodesSources[] fooBarHidden nodes-sources array
     */
    #[
        Serializer\Exclude,
        SymfonySerializer\Ignore,
        Serializer\VirtualProperty,
        Serializer\SerializedName("fooBarHidden"),
        Serializer\Type("array<mock\Entity\NodesSources>")
    ]
    public function getFooBarHiddenSources(): array
    {
        if (null === $this->fooBarHiddenSources) {
            if (
                null !== $this->objectManager &&
                null !== $this->getNode() &&
                null !== $this->getNode()->getNodeType()
            ) {
                $this->fooBarHiddenSources = $this->objectManager
                    ->getRepository(\mock\Entity\NodesSources::class)
                    ->findByNodesSourcesAndFieldAndTranslation(
                        $this,
                        $this->getNode()->getNodeType()->getFieldByName("foo_bar_hidden")
                    );
            } else {
                $this->fooBarHiddenSources = [];
            }
        }
        return $this->fooBarHiddenSources;
    }

    /**
     * @param \mock\Entity\NodesSources[]|null $fooBarHiddenSources
     *
     * @return $this
     */
    public function setFooBarHiddenSources(?array $fooBarHiddenSources): static
    {
        $this->fooBarHiddenSources = $fooBarHiddenSources;

        return $this;
    }


    /**
     * fooBarTypedSources NodesSources direct field buffer.
     * (Virtual field, this var is a buffer)
     *
     * ForBar nodes typed field.
     * Default values: MockTwo
     * @var \tests\mocks\GeneratedNodesSources\NSMockTwo[]|null
     */
    #[
        Serializer\Exclude,
        SymfonySerializer\SerializedName(serializedName: "fooBarTyped"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default", "nodes_sources_nodes"]),
        SymfonySerializer\MaxDepth(2)
    ]
    private ?array $fooBarTypedSources = null;

    /**
     * @return \tests\mocks\GeneratedNodesSources\NSMockTwo[] fooBarTyped nodes-sources array
     */
    #[
        Serializer\Groups(["nodes_sources", "nodes_sources_default", "nodes_sources_nodes"]),
        Serializer\MaxDepth(2),
        Serializer\VirtualProperty,
        Serializer\SerializedName("fooBarTyped"),
        Serializer\Type("array<mock\Entity\NodesSources>")
    ]
    public function getFooBarTypedSources(): array
    {
        if (null === $this->fooBarTypedSources) {
            if (
                null !== $this->objectManager &&
                null !== $this->getNode() &&
                null !== $this->getNode()->getNodeType()
            ) {
                $this->fooBarTypedSources = $this->objectManager
                    ->getRepository(\tests\mocks\GeneratedNodesSources\NSMockTwo::class)
                    ->findByNodesSourcesAndFieldAndTranslation(
                        $this,
                        $this->getNode()->getNodeType()->getFieldByName("foo_bar_typed")
                    );
            } else {
                $this->fooBarTypedSources = [];
            }
        }
        return $this->fooBarTypedSources;
    }

    /**
     * @param \tests\mocks\GeneratedNodesSources\NSMockTwo[]|null $fooBarTypedSources
     *
     * @return $this
     */
    public function setFooBarTypedSources(?array $fooBarTypedSources): static
    {
        $this->fooBarTypedSources = $fooBarTypedSources;

        return $this;
    }


    /**
     * ForBar layout enum.
     * Default values: light, dark, transparent
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "layout"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
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
     * For many_to_one field.
     * Default values: classname: \MyCustomEntity
     *     displayable: getName
     * @var \MyCustomEntity|null
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "fooManyToOne"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        SymfonySerializer\MaxDepth(2),
        ORM\ManyToOne(targetEntity: \MyCustomEntity::class),
        ORM\JoinColumn(name: "foo_many_to_one_id", referencedColumnName: "id", onDelete: "SET NULL"),
        ApiFilter(OrmFilter\SearchFilter::class, strategy: "exact"),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(2)
    ]
    private ?\MyCustomEntity $fooManyToOne = null;

    /**
     * @return \MyCustomEntity|null
     */
    public function getFooManyToOne(): ?\MyCustomEntity
    {
        return $this->fooManyToOne;
    }

    /**
     * @param \MyCustomEntity|null $fooManyToOne
     * @return $this
     */
    public function setFooManyToOne(?\MyCustomEntity $fooManyToOne = null): static
    {
        $this->fooManyToOne = $fooManyToOne;

        return $this;
    }


    /**
     * For many_to_many field.
     * Default values: classname: \MyCustomEntity
     *     displayable: getName
     *     orderBy:
     *         - field: name
     *           direction: asc
     * @var Collection<int, \MyCustomEntity>
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "fooManyToMany"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        SymfonySerializer\MaxDepth(2),
        ORM\ManyToMany(targetEntity: \MyCustomEntity::class),
        ORM\JoinTable(name: "node_type_foo_many_to_many"),
        ORM\JoinColumn(name: "node_type_id", referencedColumnName: "id", onDelete: "CASCADE"),
        ORM\InverseJoinColumn(name: "foo_many_to_many_id", referencedColumnName: "id", onDelete: "CASCADE"),
        ORM\OrderBy(["name" => "asc"]),
        ApiFilter(OrmFilter\SearchFilter::class, strategy: "exact"),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(2)
    ]
    private Collection $fooManyToMany;

    /**
     * @return Collection<int, \MyCustomEntity>
     */
    public function getFooManyToMany(): Collection
    {
        return $this->fooManyToMany;
    }

    /**
     * @param Collection<int, \MyCustomEntity>|\MyCustomEntity[] $fooManyToMany
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
     * For many_to_many proxied field
     *
     * @var Collection<int, \Themes\MyTheme\Entities\PositionedCity>
     */
    #[
        Serializer\Exclude,
        SymfonySerializer\Ignore,
        ORM\OneToMany(
            targetEntity: \Themes\MyTheme\Entities\PositionedCity::class,
            mappedBy: "nodeSource",
            orphanRemoval: true,
            cascade: ["persist", "remove"]
        ),
        ORM\OrderBy(["position" => "ASC"])
    ]
    private Collection $fooManyToManyProxiedProxy;

    /**
     * @return Collection<int, \Themes\MyTheme\Entities\PositionedCity>
     */
    public function getFooManyToManyProxiedProxy(): Collection
    {
        return $this->fooManyToManyProxiedProxy;
    }

    /**
     * @return Collection
     */
    #[
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(1),
        Serializer\VirtualProperty,
        Serializer\SerializedName("fooManyToManyProxied"),
        SymfonySerializer\SerializedName(serializedName: "fooManyToManyProxied"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        SymfonySerializer\MaxDepth(1)
    ]
    public function getFooManyToManyProxied(): Collection
    {
        return $this->fooManyToManyProxiedProxy->map(function (\Themes\MyTheme\Entities\PositionedCity $proxyEntity) {
            return $proxyEntity->getCity();
        });
    }

    /**
     * @param Collection $fooManyToManyProxiedProxy
     * @Serializer\VirtualProperty()
     * @return $this
     */
    public function setFooManyToManyProxiedProxy(Collection $fooManyToManyProxiedProxy): static
    {
        $this->fooManyToManyProxiedProxy = $fooManyToManyProxiedProxy;

        return $this;
    }
    /**
     * @param Collection|array|null $fooManyToManyProxied
     * @return $this
     */
    public function setFooManyToManyProxied(Collection|array|null $fooManyToManyProxied = null): static
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

    #[
        Serializer\VirtualProperty,
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\SerializedName("@type"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        SymfonySerializer\SerializedName(serializedName: "@type")
    ]
    public function getNodeTypeName(): string
    {
        return 'Mock';
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

    public function __toString(): string
    {
        return '[NSMock] ' . parent::__toString();
    }
}
