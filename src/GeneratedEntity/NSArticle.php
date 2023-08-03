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
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter as OrmFilter;
use ApiPlatform\Serializer\Filter\PropertyFilter;

/**
 * DO NOT EDIT
 * Generated custom node-source type by Roadiz.
 */
#[
    Gedmo\Loggable(logEntryClass: \RZ\Roadiz\CoreBundle\Entity\UserLogEntry::class),
    ORM\Entity(repositoryClass: \App\GeneratedEntity\Repository\NSArticleRepository::class),
    ORM\Table(name: "ns_article"),
    ApiFilter(PropertyFilter::class)
]
class NSArticle extends \RZ\Roadiz\CoreBundle\Entity\NodesSources
{
    /**
     * Your content.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "content"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        SymfonySerializer\MaxDepth(2),
        Gedmo\Versioned,
        ORM\Column(name: "content", type: "text", nullable: true),
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\MaxDepth(2),
        Serializer\Type("string")
    ]
    private ?string $content = null;

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string|null $content
     *
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
     * Secret realm_b.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "realmBSecret"),
        SymfonySerializer\Groups(["realm_b"]),
        SymfonySerializer\MaxDepth(2),
        Gedmo\Versioned,
        ORM\Column(
            name: "realm_b_secret",
            type: "string",
            nullable: true,
            length: 250
        ),
        Serializer\Groups(["realm_b"]),
        Serializer\MaxDepth(2),
        Serializer\Type("string")
    ]
    private ?string $realmBSecret = null;

    /**
     * @return string|null
     */
    public function getRealmBSecret(): ?string
    {
        return $this->realmBSecret;
    }

    /**
     * @param string|null $realmBSecret
     *
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
     * Secret realm_a.
     */
    #[
        SymfonySerializer\SerializedName(serializedName: "realmASecret"),
        SymfonySerializer\Groups(["realm_a"]),
        SymfonySerializer\MaxDepth(2),
        Gedmo\Versioned,
        ORM\Column(
            name: "realm_a_secret",
            type: "string",
            nullable: true,
            length: 250
        ),
        Serializer\Groups(["realm_a"]),
        Serializer\MaxDepth(2),
        Serializer\Type("string")
    ]
    private ?string $realmASecret = null;

    /**
     * @return string|null
     */
    public function getRealmASecret(): ?string
    {
        return $this->realmASecret;
    }

    /**
     * @param string|null $realmASecret
     *
     * @return $this
     */
    public function setRealmASecret(?string $realmASecret): static
    {
        $this->realmASecret = null !== $realmASecret ?
            (string) $realmASecret :
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
        return 'Article';
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
        return true;
    }

    public function __toString(): string
    {
        return '[NSArticle] ' . parent::__toString();
    }
}
