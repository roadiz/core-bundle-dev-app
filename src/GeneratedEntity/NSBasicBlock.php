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
 * BasicBlock node-source entity.
 */
#[Gedmo\Loggable(logEntryClass: UserLogEntry::class)]
#[ORM\Entity(repositoryClass: Repository\NSBasicBlockRepository::class)]
#[ORM\Table(name: 'ns_basicblock')]
#[ApiFilter(PropertyFilter::class)]
class NSBasicBlock extends NodesSources
{
    /** Content. */
    #[Serializer\SerializedName(serializedName: 'content')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Content')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'content', type: 'text', nullable: true)]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    #[JMS\Type('string')]
    private ?string $content = null;

    /** Boolean field. */
    #[Serializer\SerializedName(serializedName: 'booleanField')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Boolean field')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'boolean_field', type: 'boolean', nullable: false, options: ['default' => false])]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    #[JMS\Type('bool')]
    private bool $booleanField = false;

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
     * @return bool
     */
    public function getBooleanField(): bool
    {
        return $this->booleanField;
    }

    /**
     * @return $this
     */
    public function setBooleanField(bool $booleanField): static
    {
        $this->booleanField = $booleanField;
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
        return 'BasicBlock';
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
        return '[NSBasicBlock] ' . parent::__toString();
    }
}
