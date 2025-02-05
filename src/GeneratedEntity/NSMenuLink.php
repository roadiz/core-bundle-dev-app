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
 * MenuLink node-source entity.
 */
#[Gedmo\Loggable(logEntryClass: UserLogEntry::class)]
#[ORM\Entity(repositoryClass: Repository\NSMenuLinkRepository::class)]
#[ORM\Table(name: 'ns_menulink')]
#[ApiFilter(PropertyFilter::class)]
class NSMenuLink extends NodesSources
{
    /** URL externe. */
    #[Serializer\SerializedName(serializedName: 'linkExternalUrl')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'URL externe')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'link_external_url', type: 'string', nullable: true, length: 250)]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    #[JMS\Type('string')]
    private ?string $linkExternalUrl = null;

    /**
     * linkInternalReferenceSources NodesSources direct field buffer.
     * @var \RZ\Roadiz\CoreBundle\Entity\NodesSources[]|null
     * Référence au nœud (Page ou Bloc de page).
     * Default values:
     * - Page
     * - Article
     */
    #[JMS\Exclude]
    #[Serializer\SerializedName(serializedName: 'linkInternalReference')]
    #[Serializer\Groups(['urls'])]
    #[ApiProperty(description: 'Référence au nœud (Page ou Bloc de page)')]
    #[Serializer\MaxDepth(1)]
    #[Serializer\Context(
        normalizationContext: [
        'groups' => ['urls', 'nodes_sources_base'],
        'skip_null_value' => true,
        'jsonld_embed_context' => false,
        'enable_max_depth' => true,
    ],
        groups: ['urls'],
    )]
    private ?array $linkInternalReferenceSources = null;

    /**
     * Image.
     * (Virtual field, this var is a buffer)
     */
    #[JMS\Exclude]
    #[Serializer\SerializedName(serializedName: 'image')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default', 'nodes_sources_documents'])]
    #[ApiProperty(description: 'Image')]
    #[Serializer\MaxDepth(2)]
    private ?array $image = null;

    /**
     * @return string|null
     */
    public function getLinkExternalUrl(): ?string
    {
        return $this->linkExternalUrl;
    }

    /**
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
     * @return \RZ\Roadiz\CoreBundle\Entity\NodesSources[]
     */
    #[JMS\Groups(['urls'])]
    #[JMS\MaxDepth(1)]
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('linkInternalReference')]
    #[JMS\Type('array<RZ\Roadiz\CoreBundle\Entity\NodesSources>')]
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
     * @return $this
     */
    public function setLinkInternalReferenceSources(?array $linkInternalReferenceSources): static
    {
        $this->linkInternalReferenceSources = $linkInternalReferenceSources;
        return $this;
    }

    /**
     * @return \RZ\Roadiz\CoreBundle\Entity\Document[]
     */
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default', 'nodes_sources_documents'])]
    #[JMS\MaxDepth(2)]
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('image')]
    #[JMS\Type('array<RZ\Roadiz\CoreBundle\Entity\Document>')]
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
     * @return $this
     */
    public function addImage(\RZ\Roadiz\CoreBundle\Entity\Document $document): static
    {
        if (null === $this->objectManager) {
            return $this;
        }
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
        return $this;
    }

    #[JMS\VirtualProperty]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\SerializedName('@type')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[Serializer\SerializedName(serializedName: '@type')]
    public function getNodeTypeName(): string
    {
        return 'MenuLink';
    }

    #[JMS\VirtualProperty]
    #[JMS\Groups(['node_type'])]
    #[JMS\SerializedName('nodeTypeColor')]
    #[Serializer\Groups(['node_type'])]
    #[Serializer\SerializedName(serializedName: 'nodeTypeColor')]
    public function getNodeTypeColor(): string
    {
        return '#6369c2';
    }

    /**
     * $this->nodeType->isReachable() proxy.
     * @return bool Does this nodeSource is reachable over network?
     */
    #[JMS\VirtualProperty]
    public function isReachable(): bool
    {
        return false;
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
        return '[NSMenuLink] ' . parent::__toString();
    }
}
