<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use RZ\Roadiz\CoreBundle\Api\Dto\NodesSourcesDto;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @deprecated Use NodesSourcesPathNormalizer
 */
abstract class NodesSourcesOutputDataTransformer implements DataTransformerInterface
{
    protected UrlGeneratorInterface $urlGenerator;

    /**
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->urlGenerator = $urlGenerator;
    }

    protected function transformNodesSources(
        NodesSourcesDto $output,
        NodesSources $data,
        array $context = []
    ): NodesSourcesDto {
        $output->title = $data->getTitle();
        $output->node = $data->getNode();
        $output->metaTitle = $data->getMetaTitle();
        $output->metaDescription = $data->getMetaDescription();
        $output->translation =  $data->getTranslation();
        $output->slug = $data->getIdentifier();
        if ($data->isPublishable()) {
            $output->publishedAt = $data->getPublishedAt();
        }
        return $output;
    }

    /**
     * @inheritDoc
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return $data instanceof NodesSources;
    }
}
