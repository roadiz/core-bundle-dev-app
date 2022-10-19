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
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter as OrmFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;

/**
 * DO NOT EDIT
 * Generated custom node-source type by Roadiz.
 */
#[
    Gedmo\Loggable(logEntryClass: \RZ\Roadiz\CoreBundle\Entity\UserLogEntry::class),
    ORM\Entity(repositoryClass: \App\GeneratedEntity\Repository\NSBasicBlockRepository::class),
    ORM\Table(name: "ns_basicblock"),
    ApiFilter(PropertyFilter::class)
]
class NSBasicBlock extends \RZ\Roadiz\CoreBundle\Entity\NodesSources
{
    /**
     * Content.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "content"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
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
     * Boolean field.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "booleanField"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        SymfonySerializer\MaxDepth(2),
        Gedmo\Versioned,
        ORM\Column(
            name: "boolean_field",
            type: "boolean",
            nullable: false,
            options: ["default" => false]
        ),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(2),
        Serializer\Type("bool")
    ]
    private bool $booleanField = false;

    /**
     * @return bool
     */
    public function getBooleanField(): bool
    {
        return $this->booleanField;
    }

    /**
     * @param bool $booleanField
     *
     * @return $this
     */
    public function setBooleanField(bool $booleanField): static
    {
        $this->booleanField = $booleanField;

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
            if (
                null !== $this->objectManager &&
                null !== $this->getNode() &&
                null !== $this->getNode()->getNodeType()
            ) {
                $this->image = $this->objectManager
                    ->getRepository(\RZ\Roadiz\CoreBundle\Entity\Document::class)
                    ->findByNodeSourceAndField(
                        $this,
                        $this->getNode()->getNodeType()->getFieldByName("image")
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
        if (
            null !== $this->objectManager &&
            null !== $this->getNode() &&
            null !== $this->getNode()->getNodeType()
        ) {
            $field = $this->getNode()->getNodeType()->getFieldByName("image");
            if (null !== $field) {
                $nodeSourceDocument = new \RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments(
                    $this,
                    $document,
                    $field
                );
                if (!$this->hasNodesSourcesDocuments($nodeSourceDocument)) {
                    $this->objectManager->persist($nodeSourceDocument);
                    $this->addDocumentsByFields($nodeSourceDocument);
                    $this->image = null;
                }
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
        return 'BasicBlock';
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

    public function __toString()
    {
        return '[NSBasicBlock] ' . parent::__toString();
    }
}
