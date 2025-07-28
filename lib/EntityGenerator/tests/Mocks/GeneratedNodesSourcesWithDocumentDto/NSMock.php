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
use Symfony\Component\Validator\Constraints as Assert;
use mock\Entity\NodesSources;

/**
 * Mock node-source entity.
 */
#[Gedmo\Loggable(logEntryClass: UserLogEntry::class)]
#[ORM\Entity(repositoryClass: \mock\Entity\Repository\NodesSourcesRepository::class)]
#[ORM\Table(name: 'ns_mock')]
#[ApiFilter(PropertyFilter::class)]
class NSMock extends NodesSources
{
    /**
     * Bar documents field.
     * Maecenas sed diam eget risus varius blandit sit amet non magna.
     * @var \RZ\Roadiz\CoreBundle\Model\DocumentDto[]|null
     * (Virtual field, this var is a buffer)
     */
    #[Serializer\SerializedName(serializedName: 'bar')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default', 'nodes_sources_documents'])]
    #[ApiProperty(description: 'Bar documents field: Maecenas sed diam eget risus varius blandit sit amet non magna', genId: true)]
    #[Serializer\MaxDepth(1)]
    private ?array $bar = null;

    /**
     * @return \RZ\Roadiz\CoreBundle\Model\DocumentDto[]
     */
    public function getBar(): array
    {
        if (null === $this->bar) {
            if (null !== $this->objectManager) {
                $this->bar = $this->objectManager
                    ->getRepository(\mock\Entity\Document::class)
                    ->findDocumentDtoByNodeSourceAndFieldName(
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

    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[Serializer\SerializedName(serializedName: '@type')]
    #[\Override]
    public function getNodeTypeName(): string
    {
        return 'Mock';
    }

    #[Serializer\Groups(['node_type'])]
    #[Serializer\SerializedName(serializedName: 'nodeTypeColor')]
    #[\Override]
    public function getNodeTypeColor(): string
    {
        return '';
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
        return true;
    }

    #[\Override]
    public function __toString(): string
    {
        return '[NSMock] ' . parent::__toString();
    }
}
