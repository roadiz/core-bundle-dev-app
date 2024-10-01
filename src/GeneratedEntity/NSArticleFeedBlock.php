<?php

/**
 * THIS IS A GENERATED FILE, DO NOT EDIT IT.
 * IT WILL BE RECREATED AT EACH NODE-TYPE UPDATE.
 */

declare(strict_types=1);

namespace App\GeneratedEntity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Component\Serializer\Attribute as Serializer;

/**
 * ArticleFeedBlock node-source entity.
 */
#[Gedmo\Loggable(logEntryClass: \RZ\Roadiz\CoreBundle\Entity\UserLogEntry::class)]
#[ORM\Entity(repositoryClass: \App\GeneratedEntity\Repository\NSArticleFeedBlockRepository::class)]
#[ORM\Table(name: 'ns_articlefeedblock')]
#[ApiFilter(\ApiPlatform\Serializer\Filter\PropertyFilter::class)]
class NSArticleFeedBlock extends \RZ\Roadiz\CoreBundle\Entity\NodesSources
{
    /** Article count. */
    #[Serializer\SerializedName(serializedName: 'listingCount')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Article count')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'listing_count', type: 'integer', nullable: true)]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    #[JMS\Type('int')]
    private int|float|null $listingCount = null;

    /**
     * @return int|float|null
     */
    public function getListingCount(): int|float|null
    {
        return $this->listingCount;
    }

    /**
     * @return $this
     */
    public function setListingCount(int|float|null $listingCount): static
    {
        $this->listingCount = null !== $listingCount ?
                    (int) $listingCount :
                    null;
        return $this;
    }

    #[JMS\VirtualProperty]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\SerializedName('@type')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[Serializer\SerializedName(serializedName: '@type')]
    public function getNodeTypeName(): string
    {
        return 'ArticleFeedBlock';
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
        return '[NSArticleFeedBlock] ' . parent::__toString();
    }
}
