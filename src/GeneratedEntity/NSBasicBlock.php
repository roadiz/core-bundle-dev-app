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
    private ?string $content = null;

    /** Contenttest. */
    #[Serializer\SerializedName(serializedName: 'contenttest')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Contenttest')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'contenttest', type: 'text', nullable: true)]
    private ?string $contenttest = null;

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

    /** Boolean field. */
    #[Serializer\SerializedName(serializedName: 'booleanField')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Boolean field')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'boolean_field', type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $booleanField = false;

    /**
     * Image.
     * (Virtual field, this var is a buffer)
     */
    #[Serializer\SerializedName(serializedName: 'image')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default', 'nodes_sources_documents'])]
    #[ApiProperty(description: 'Image', genId: true)]
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
     * @return string|null
     */
    public function getContenttest(): ?string
    {
        return $this->contenttest;
    }

    /**
     * @return $this
     */
    public function setContenttest(?string $contenttest): static
    {
        $this->contenttest = null !== $contenttest ?
                    (string) $contenttest :
                    null;
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
     * @return \RZ\Roadiz\CoreBundle\Model\DocumentDto[]
     */
    public function getImage(): array
    {
        if (null === $this->image) {
            if (null !== $this->objectManager) {
                $this->image = $this->objectManager
                    ->getRepository(\RZ\Roadiz\CoreBundle\Entity\Document::class)
                    ->findDocumentDtoByNodeSourceAndFieldName(
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

    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[Serializer\SerializedName(serializedName: '@type')]
    #[\Override]
    public function getNodeTypeName(): string
    {
        return 'BasicBlock';
    }

    #[Serializer\Groups(['node_type'])]
    #[Serializer\SerializedName(serializedName: 'nodeTypeColor')]
    #[\Override]
    public function getNodeTypeColor(): string
    {
        return '#69a5ff';
    }

    /**
     * $this->nodeType->isReachable() proxy.
     * @return bool Does this nodeSource is reachable over network?
     */
    #[\Override]
    public function isReachable(): bool
    {
        return false;
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
        return '[NSBasicBlock] ' . parent::__toString();
    }
}
