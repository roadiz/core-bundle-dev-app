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
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\UserLogEntry;
use Symfony\Component\Serializer\Attribute as Serializer;

/**
 * Neutral node-source entity.
 * Neutral.
 */
#[Gedmo\Loggable(logEntryClass: UserLogEntry::class)]
#[ORM\Entity(repositoryClass: Repository\NSNeutralRepository::class)]
#[ORM\Table(name: 'ns_neutral')]
#[ORM\Index(columns: ['number'])]
#[ApiFilter(PropertyFilter::class)]
class NSNeutral extends NodesSources
{
    /** Number. */
    #[Serializer\SerializedName(serializedName: 'number')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Number')]
    #[Serializer\MaxDepth(2)]
    #[ApiFilter(Filter\OrderFilter::class)]
    #[ApiFilter(Filter\NumericFilter::class)]
    #[ApiFilter(Filter\RangeFilter::class)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'number', type: 'integer', nullable: true)]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    #[JMS\Type('int')]
    private int|float|null $number = null;

    public function getNumber(): int|float|null
    {
        return $this->number;
    }

    /**
     * @return $this
     */
    public function setNumber(int|float|null $number): static
    {
        $this->number = null !== $number ?
                    (int) $number :
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
        return 'Neutral';
    }

    /**
     * $this->nodeType->isReachable() proxy.
     *
     * @return bool Does this nodeSource is reachable over network?
     */
    #[JMS\VirtualProperty]
    public function isReachable(): bool
    {
        return false;
    }

    /**
     * $this->nodeType->isPublishable() proxy.
     *
     * @return bool Does this nodeSource is publishable with date and time?
     */
    #[JMS\VirtualProperty]
    public function isPublishable(): bool
    {
        return false;
    }

    public function __toString(): string
    {
        return '[NSNeutral] '.parent::__toString();
    }
}
