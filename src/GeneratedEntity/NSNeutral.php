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
    ORM\Entity(repositoryClass: \App\GeneratedEntity\Repository\NSNeutralRepository::class),
    ORM\Table(name: "ns_neutral"),
    ORM\Index(columns: ["number"]),
    ApiFilter(PropertyFilter::class)
]
class NSNeutral extends \RZ\Roadiz\CoreBundle\Entity\NodesSources
{
    /**
     * Number.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "number"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        SymfonySerializer\MaxDepth(2),
        ApiFilter(OrmFilter\OrderFilter::class),
        ApiFilter(OrmFilter\NumericFilter::class),
        ApiFilter(OrmFilter\RangeFilter::class),
        Gedmo\Versioned,
        ORM\Column(name: "number", type: "integer", nullable: true),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(2),
        Serializer\Type("int")
    ]
    private int|float|null $number = null;

    /**
     * @return int|float|null
     */
    public function getNumber(): int|float|null
    {
        return $this->number;
    }

    /**
     * @param int|float|null $number
     *
     * @return $this
     */
    public function setNumber(int|float|null $number): static
    {
        $this->number = null !== $number ?
            (int) $number :
            null;

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
        return 'Neutral';
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
        return '[NSNeutral] ' . parent::__toString();
    }
}
