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
    ORM\Entity(repositoryClass: \App\GeneratedEntity\Repository\NSOfferRepository::class),
    ORM\Table(name: "ns_offer"),
    ORM\Index(columns: ["price"]),
    ApiFilter(PropertyFilter::class)
]
class NSOffer extends \RZ\Roadiz\CoreBundle\Entity\NodesSources
{
    /**
     * Price.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "price"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        SymfonySerializer\MaxDepth(2),
        ApiFilter(OrmFilter\OrderFilter::class),
        ApiFilter(OrmFilter\NumericFilter::class),
        ApiFilter(OrmFilter\RangeFilter::class),
        Gedmo\Versioned,
        ORM\Column(name: "price", type: "integer", nullable: true),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(2),
        Serializer\Type("int")
    ]
    private int|float|null $price = null;

    /**
     * @return int|float|null
     */
    public function getPrice(): int|float|null
    {
        return $this->price;
    }

    /**
     * @param int|float|null $price
     *
     * @return $this
     */
    public function setPrice(int|float|null $price)
    {
        $this->price = null !== $price ?
            (int) $price :
            null;

        return $this;
    }


    /**
     * VAT.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "vat"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        SymfonySerializer\MaxDepth(2),
        Gedmo\Versioned,
        ORM\Column(
            name: "vat",
            type: "decimal",
            nullable: true,
            precision: 18,
            scale: 3
        ),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(2),
        Serializer\Type("double")
    ]
    private int|float|null $vat = null;

    /**
     * @return int|float|null
     */
    public function getVat(): int|float|null
    {
        return $this->vat;
    }

    /**
     * @param int|float|null $vat
     *
     * @return $this
     */
    public function setVat(int|float|null $vat)
    {
        $this->vat = $vat;

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
        return 'Offer';
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
        return false;
    }

    public function __toString()
    {
        return '[NSOffer] ' . parent::__toString();
    }
}
