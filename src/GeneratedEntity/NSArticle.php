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
 * Article node-source entity.
 * Article
 */
#[Gedmo\Loggable(logEntryClass: UserLogEntry::class)]
#[ORM\Entity(repositoryClass: Repository\NSArticleRepository::class)]
#[ORM\Table(name: 'ns_article')]
#[ORM\Index(columns: ['unpublished_at'])]
#[ApiFilter(PropertyFilter::class)]
class NSArticle extends NodesSources
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
    #[ApiFilter(Filter\OrderFilter::class)]
    #[ApiFilter(Filter\DateFilter::class)]
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
     * relatedArticleSources NodesSources direct field buffer.
     * @var \App\GeneratedEntity\NSArticle[]|null
     * Related article.
     * Default values:
     * Article
     */
    #[JMS\Exclude]
    #[Serializer\SerializedName(serializedName: 'relatedArticle')]
    #[Serializer\Groups(['nodes_sources', 'nodes_sources_default', 'nodes_sources_nodes'])]
    #[ApiProperty(description: 'Related article')]
    #[Serializer\MaxDepth(2)]
    #[Serializer\Context(
        normalizationContext: ['groups' => ['related_articles']],
        groups: ['nodes_sources', 'nodes_sources_default', 'nodes_sources_nodes'],
    )]
    private ?array $relatedArticleSources = null;

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

    /**
     * @return \App\GeneratedEntity\NSArticle[]
     */
    #[JMS\Groups(['nodes_sources', 'nodes_sources_default', 'nodes_sources_nodes'])]
    #[JMS\MaxDepth(2)]
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('relatedArticle')]
    #[JMS\Type('array<RZ\Roadiz\CoreBundle\Entity\NodesSources>')]
    public function getRelatedArticleSources(): array
    {
        if (null === $this->relatedArticleSources) {
            if (null !== $this->objectManager) {
                $this->relatedArticleSources = $this->objectManager
                    ->getRepository(\App\GeneratedEntity\NSArticle::class)
                    ->findByNodesSourcesAndFieldNameAndTranslation(
                        $this,
                        'related_article'
                    );
            } else {
                $this->relatedArticleSources = [];
            }
        }
        return $this->relatedArticleSources;
    }

    /**
     * @param \App\GeneratedEntity\NSArticle[]|null $relatedArticleSources
     * @return $this
     */
    public function setRelatedArticleSources(?array $relatedArticleSources): static
    {
        $this->relatedArticleSources = $relatedArticleSources;
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
