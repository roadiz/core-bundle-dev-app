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
 * Offer node-source entity.
 * Offer
 */
#[Gedmo\Loggable(logEntryClass: UserLogEntry::class)]
#[ORM\Entity(repositoryClass: Repository\NSOfferRepository::class)]
#[ORM\Table(name: 'ns_offer')]
#[ORM\Index(columns: ['price'])]
#[ORM\Index(columns: ['layout'])]
#[ApiFilter(PropertyFilter::class)]
class NSOffer extends NodesSources
{
    /** VAT. */
    #[Serializer\SerializedName(serializedName: 'vat')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'VAT')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'vat', type: 'decimal', nullable: true, precision: 18, scale: 3)]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    #[JMS\Type('double')]
    private int|float|null $vat = null;

    /** Price. */
    #[Serializer\SerializedName(serializedName: 'price')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Price')]
    #[Serializer\MaxDepth(2)]
    #[ApiFilter(Filter\OrderFilter::class)]
    #[ApiFilter(Filter\NumericFilter::class)]
    #[ApiFilter(Filter\RangeFilter::class)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'price', type: 'integer', nullable: true)]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    #[JMS\Type('int')]
    private int|float|null $price = null;

    /** Geolocation. */
    #[Serializer\SerializedName(serializedName: 'geolocation')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Geolocation')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'geolocation', type: 'json', nullable: true)]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    private mixed $geolocation = null;

    /** Multi geolocations. */
    #[Serializer\SerializedName(serializedName: 'multiGeolocation')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Multi geolocations')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'multi_geolocation', type: 'json', nullable: true)]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    private mixed $multiGeolocation = null;

    /** Layout. */
    #[Serializer\SerializedName(serializedName: 'layout')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Layout', example: 'light')]
    #[Serializer\MaxDepth(2)]
    #[ApiFilter(Filter\SearchFilter::class, strategy: 'exact')]
    #[ApiFilter(\RZ\Roadiz\CoreBundle\Api\Filter\NotFilter::class)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'layout', type: 'string', nullable: true, length: 250)]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    #[JMS\Type('string')]
    private ?string $layout = null;

    /**
     * @return int|float|null
     */
    public function getVat(): int|float|null
    {
        return $this->vat;
    }

    /**
     * @return $this
     */
    public function setVat(int|float|null $vat): static
    {
        $this->vat = $vat;
        return $this;
    }

    /**
     * @return int|float|null
     */
    public function getPrice(): int|float|null
    {
        return $this->price;
    }

    /**
     * @return $this
     */
    public function setPrice(int|float|null $price): static
    {
        $this->price = null !== $price ?
                    (int) $price :
                    null;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGeolocation(): mixed
    {
        return $this->geolocation;
    }

    /**
     * @return $this
     */
    public function setGeolocation(mixed $geolocation): static
    {
        $this->geolocation = $geolocation;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMultiGeolocation(): mixed
    {
        return $this->multiGeolocation;
    }

    /**
     * @return $this
     */
    public function setMultiGeolocation(mixed $multiGeolocation): static
    {
        $this->multiGeolocation = $multiGeolocation;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLayout(): ?string
    {
        return $this->layout;
    }

    /**
     * @return $this
     */
    public function setLayout(?string $layout): static
    {
        $this->layout = null !== $layout ?
                    (string) $layout :
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
        return 'Offer';
    }

    #[JMS\VirtualProperty]
    #[JMS\Groups(['node_type'])]
    #[JMS\SerializedName('nodeTypeColor')]
    #[Serializer\Groups(['node_type'])]
    #[Serializer\SerializedName(serializedName: 'nodeTypeColor')]
    public function getNodeTypeColor(): string
    {
        return '#ff0000';
    }

    /**
     * $this->nodeType->isReachable() proxy.
     * @return bool Does this nodeSource is reachable over network?
     */
    #[JMS\VirtualProperty]
    public function isReachable(): bool
    {
        return true;
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
        return '[NSOffer] ' . parent::__toString();
    }
}
