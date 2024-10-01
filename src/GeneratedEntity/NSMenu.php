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
 * Menu node-source entity.
 */
#[Gedmo\Loggable(logEntryClass: \RZ\Roadiz\CoreBundle\Entity\UserLogEntry::class)]
#[ORM\Entity(repositoryClass: \App\GeneratedEntity\Repository\NSMenuRepository::class)]
#[ORM\Table(name: 'ns_menu')]
#[ApiFilter(\ApiPlatform\Serializer\Filter\PropertyFilter::class)]
class NSMenu extends \RZ\Roadiz\CoreBundle\Entity\NodesSources
{
    #[JMS\VirtualProperty]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\SerializedName('@type')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[Serializer\SerializedName(serializedName: '@type')]
    public function getNodeTypeName(): string
    {
        return 'Menu';
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
        return '[NSMenu] ' . parent::__toString();
    }
}
