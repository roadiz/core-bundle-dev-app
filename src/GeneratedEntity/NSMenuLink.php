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
 * MenuLink node-source entity.
 */
#[
    Gedmo\Loggable(logEntryClass: \RZ\Roadiz\CoreBundle\Entity\UserLogEntry::class),
    ORM\Entity(repositoryClass: \App\GeneratedEntity\Repository\NSMenuLinkRepository::class),
    ORM\Table(name: "ns_menulink"),
    ApiFilter(PropertyFilter::class)
]
class NSMenuLink extends \RZ\Roadiz\CoreBundle\Entity\NodesSources
{
    /**
     * URL externe.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "linkExternalUrl"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        \ApiPlatform\Metadata\ApiProperty(description: "URL externe"),
        SymfonySerializer\MaxDepth(2),
        Gedmo\Versioned,
        ORM\Column(
            name: "link_external_url",
            type: "string",
            nullable: true,
            length: 250
        ),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(2),
        Serializer\Type("string")
    ]
    private ?string $linkExternalUrl = null;

    /**
     * @return string|null
     */
    public function getLinkExternalUrl(): ?string
    {
        return $this->linkExternalUrl;
    }

    /**
     * @param string|null $linkExternalUrl
     *
     * @return $this
     */
    public function setLinkExternalUrl(?string $linkExternalUrl): static
    {
        $this->linkExternalUrl = null !== $linkExternalUrl ?
            (string) $linkExternalUrl :
            null;

        return $this;
    }


    /**
     * linkInternalReferenceSources NodesSources direct field buffer.
     * (Virtual field, this var is a buffer)
     *
     * Référence au nœud (Page ou Bloc de page).
     * Default values: Page, Article, ArticleContainer, Offer
     * @var \RZ\Roadiz\CoreBundle\Entity\NodesSources[]|null
     */
    #[
        Serializer\Exclude,
        SymfonySerializer\SerializedName(serializedName: "linkInternalReference"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default", "nodes_sources_nodes"]),
        \ApiPlatform\Metadata\ApiProperty(description: "Référence au nœud (Page ou Bloc de page)"),
        SymfonySerializer\MaxDepth(2)
    ]
    private ?array $linkInternalReferenceSources = null;

    /**
     * @return \RZ\Roadiz\CoreBundle\Entity\NodesSources[] linkInternalReference nodes-sources array
     */
    #[
        Serializer\Groups(["nodes_sources", "nodes_sources_default", "nodes_sources_nodes"]),
        Serializer\MaxDepth(2),
        Serializer\VirtualProperty,
        Serializer\SerializedName("linkInternalReference"),
        Serializer\Type("array<RZ\Roadiz\CoreBundle\Entity\NodesSources>")
    ]
    public function getLinkInternalReferenceSources(): array
    {
        if (null === $this->linkInternalReferenceSources) {
            if (null !== $this->objectManager) {
                $this->linkInternalReferenceSources = $this->objectManager
                    ->getRepository(\RZ\Roadiz\CoreBundle\Entity\NodesSources::class)
                    ->findByNodesSourcesAndFieldNameAndTranslation(
                        $this,
                        'link_internal_reference'
                    );
            } else {
                $this->linkInternalReferenceSources = [];
            }
        }
        return $this->linkInternalReferenceSources;
    }

    /**
     * @param \RZ\Roadiz\CoreBundle\Entity\NodesSources[]|null $linkInternalReferenceSources
     *
     * @return $this
     */
    public function setLinkInternalReferenceSources(?array $linkInternalReferenceSources): static
    {
        $this->linkInternalReferenceSources = $linkInternalReferenceSources;

        return $this;
    }


    /**
     * Image.
     *
     * (Virtual field, this var is a buffer)
     */
    #[
        Serializer\Exclude,
        SymfonySerializer\SerializedName(serializedName: "image"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default", "nodes_sources_documents"]),
        \ApiPlatform\Metadata\ApiProperty(description: "Image"),
        SymfonySerializer\MaxDepth(2)
    ]
    private ?array $image = null;

    /**
     * @return \RZ\Roadiz\CoreBundle\Entity\Document[] Documents array
     */
    #[
        Serializer\Groups(["nodes_sources", "nodes_sources_default", "nodes_sources_documents"]),
        Serializer\MaxDepth(2),
        Serializer\VirtualProperty,
        Serializer\SerializedName("image"),
        Serializer\Type("array<RZ\Roadiz\CoreBundle\Entity\Document>")
    ]
    public function getImage(): array
    {
        if (null === $this->image) {
            if (null !== $this->objectManager) {
                $this->image = $this->objectManager
                    ->getRepository(\RZ\Roadiz\CoreBundle\Entity\Document::class)
                    ->findByNodeSourceAndFieldName(
                        $this,
                        'image'
                    );
            } else {
                $this->image = [];
            }
        }
        return $this->image;
    }

    /**
     * @param \RZ\Roadiz\CoreBundle\Entity\Document $document
     *
     * @return $this
     */
    public function addImage(\RZ\Roadiz\CoreBundle\Entity\Document $document): static
    {
        if (null !== $this->objectManager) {
            $nodeSourceDocument = new \RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments(
                $this,
                $document
            );
            $nodeSourceDocument->setFieldName('image');
            if (!$this->hasNodesSourcesDocuments($nodeSourceDocument)) {
                $this->objectManager->persist($nodeSourceDocument);
                $this->addDocumentsByFields($nodeSourceDocument);
                $this->image = null;
            }
        }
        return $this;
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
        return 'MenuLink';
    }

    /**
     * $this->nodeType->isReachable() proxy.
     *
     * @return bool Does this nodeSource is reachable over network?
     */
    public function isReachable(): bool
    {
        return false;
    }

    /**
     * $this->nodeType->isPublishable() proxy.
     *
     * @return bool Does this nodeSource is publishable with date and time?
     */
    public function isPublishable(): bool
    {
        return false;
    }

    public function __toString(): string
    {
        return '[NSMenuLink] ' . parent::__toString();
    }
}
