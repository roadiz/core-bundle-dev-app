<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Api\TreeWalker\Fixtures;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Model\DocumentDto;

/**
 * Mimics a generated node-source: buffered documents and references fields,
 * plus a documents field generated before DTO setters existed (no setLegacyDtos()).
 */
class NSPrefetchPage extends NodesSources
{
    /** @var DocumentDto[]|null */
    private ?array $images = null;

    /** @var DocumentDto[]|null */
    private ?array $legacy = null;

    /** @var NodesSources[]|null */
    private ?array $relatedSources = null;

    /** @var NodesSources[]|null */
    private ?array $anySources = null;

    /**
     * @return DocumentDto[]|null
     */
    public function getImagesBuffer(): ?array
    {
        return $this->images;
    }

    /**
     * @param DocumentDto[]|null $images
     */
    public function setImagesDtos(?array $images): static
    {
        $this->images = $images;

        return $this;
    }

    /**
     * @return DocumentDto[]|null
     */
    public function getLegacyBuffer(): ?array
    {
        return $this->legacy;
    }

    /**
     * @return NodesSources[]|null
     */
    public function getRelatedBuffer(): ?array
    {
        return $this->relatedSources;
    }

    /**
     * @param NodesSources[]|null $relatedSources
     */
    public function setRelatedSources(?array $relatedSources): static
    {
        $this->relatedSources = $relatedSources;

        return $this;
    }

    /**
     * @return NodesSources[]|null
     */
    public function getAnyBuffer(): ?array
    {
        return $this->anySources;
    }

    /**
     * @param NodesSources[]|null $anySources
     */
    public function setAnySources(?array $anySources): static
    {
        $this->anySources = $anySources;

        return $this;
    }

    #[\Override]
    public function getNodeTypeName(): string
    {
        return 'PrefetchPage';
    }
}
