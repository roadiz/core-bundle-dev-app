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
 * Article node-source entity.
 * Article
 */
#[Gedmo\Loggable(logEntryClass: \RZ\Roadiz\CoreBundle\Entity\UserLogEntry::class)]
#[ORM\Entity(repositoryClass: \App\GeneratedEntity\Repository\NSArticleRepository::class)]
#[ORM\Table(name: 'ns_article')]
#[ORM\Index(columns: ['unpublished_at'])]
#[ApiFilter(\ApiPlatform\Serializer\Filter\PropertyFilter::class)]
class NSArticle extends \RZ\Roadiz\CoreBundle\Entity\NodesSources
{
    /** Your content. */
    #[Serializer\SerializedName(serializedName: 'content')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Your content')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'content', type: 'text', nullable: true)]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    #[JMS\Type('string')]
    private ?string $content = null;

    /** Secret realm_b. */
    #[Serializer\SerializedName(serializedName: 'realmBSecret')]
    #[Serializer\Groups(['realm_b'])]
    #[ApiProperty(description: 'Secret realm_b')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'realm_b_secret', type: 'string', nullable: true, length: 250)]
    #[JMS\Groups(['realm_b'])]
    #[JMS\MaxDepth(2)]
    #[JMS\Type('string')]
    private ?string $realmBSecret = null;

    /** Secret realm_a. */
    #[Serializer\SerializedName(serializedName: 'realmASecret')]
    #[Serializer\Groups(['realm_a'])]
    #[ApiProperty(description: 'Secret realm_a')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'realm_a_secret', type: 'string', nullable: true, length: 250)]
    #[JMS\Groups(['realm_a'])]
    #[JMS\MaxDepth(2)]
    #[JMS\Type('string')]
    private ?string $realmASecret = null;

    /** Date de dépublication. */
    #[Serializer\SerializedName(serializedName: 'unpublishedAt')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[ApiProperty(description: 'Date de dépublication')]
    #[Serializer\MaxDepth(2)]
    #[ApiFilter(\ApiPlatform\Doctrine\Orm\Filter\OrderFilter::class)]
    #[ApiFilter(\ApiPlatform\Doctrine\Orm\Filter\DateFilter::class)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'unpublished_at', type: 'datetime', nullable: true)]
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default'])]
    #[JMS\MaxDepth(2)]
    #[JMS\Type('DateTime')]
    private ?\DateTime $unpublishedAt = null;

    /** Only on web response. */
    #[Serializer\SerializedName(serializedName: 'onlyOnWebresponse')]
    #[Serializer\Groups(['article_get_by_path'])]
    #[ApiProperty(description: 'Only on web response')]
    #[Serializer\MaxDepth(2)]
    #[Gedmo\Versioned]
    #[ORM\Column(name: 'only_on_webresponse', type: 'string', nullable: true, length: 250)]
    #[JMS\Groups(['article_get_by_path'])]
    #[JMS\MaxDepth(2)]
    #[JMS\Type('string')]
    private ?string $onlyOnWebresponse = null;

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
    public function getRealmBSecret(): ?string
    {
        return $this->realmBSecret;
    }

    /**
     * @return $this
     */
    public function setRealmBSecret(?string $realmBSecret): static
    {
        $this->realmBSecret = null !== $realmBSecret ?
                    (string) $realmBSecret :
                    null;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRealmASecret(): ?string
    {
        return $this->realmASecret;
    }

    /**
     * @return $this
     */
    public function setRealmASecret(?string $realmASecret): static
    {
        $this->realmASecret = null !== $realmASecret ?
                    (string) $realmASecret :
                    null;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getUnpublishedAt(): ?\DateTime
    {
        return $this->unpublishedAt;
    }

    /**
     * @return $this
     */
    public function setUnpublishedAt(?\DateTime $unpublishedAt): static
    {
        $this->unpublishedAt = $unpublishedAt;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOnlyOnWebresponse(): ?string
    {
        return $this->onlyOnWebresponse;
    }

    /**
     * @return $this
     */
    public function setOnlyOnWebresponse(?string $onlyOnWebresponse): static
    {
        $this->onlyOnWebresponse = null !== $onlyOnWebresponse ?
                    (string) $onlyOnWebresponse :
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
        return 'Article';
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
        return true;
    }

    public function __toString(): string
    {
        return '[NSArticle] ' . parent::__toString();
    }
}
