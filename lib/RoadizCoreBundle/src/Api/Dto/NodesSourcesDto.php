<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Dto;

use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use Symfony\Component\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @deprecated
 */
abstract class NodesSourcesDto
{
    /**
     * @var string
     */
    #[Groups(['nodes_sources', 'nodes_sources_base'])]
    public ?string $title = '';
    /**
     * @var string
     */
    #[Groups(['nodes_sources', 'nodes_sources_base'])]
    public string $metaTitle = '';
    /**
     * @var string
     */
    #[Groups(['nodes_sources', 'nodes_sources_base'])]
    public string $metaDescription = '';
    /**
     * @var string
     */
    #[Groups(['nodes_sources', 'nodes_sources_base'])]
    public string $slug = '';
    /**
     * @var \DateTime|null
     */
    #[Groups(['nodes_sources', 'nodes_sources_base'])]
    public ?\DateTime $publishedAt = null;
    /**
     * @var Node|null
     */
    #[Groups(['nodes_sources', 'nodes_sources_base'])]
    public ?Node $node = null;
    /**
     * @var TranslationInterface|null
     */
    #[Groups(['nodes_sources', 'nodes_sources_base', 'translation_base'])]
    public ?TranslationInterface $translation = null;
    /**
     * @var string|null
     * @deprecated NodesSources url is exposed via RZ\Roadiz\CoreBundle\Serializer\Normalizer\NodesSourcesPathNormalizer
     */
    #[Serializer\MaxDepth(4)]
    #[Groups(['nodes_sources', 'nodes_sources_base', 'urls'])]
    public ?string $url = null;
}
