<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Enricher;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsTaggedItem(priority: 5)]
final readonly class NodesSourcesPathEnricher implements NormalizationEnricherInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[\Override]
    public function supports(object $data, ?string $format, array $context): bool
    {
        return $data instanceof NodesSources
            && $data->isReachable()
            && isset($context['groups'])
            && \in_array('urls', $context['groups'], true);
    }

    /**
     * @param NodesSources $data
     */
    #[\Override]
    public function enrich(array $normalized, object $data, ?string $format, array $context): array
    {
        if (isset($normalized['url'])) {
            return $normalized;
        }

        $normalized['url'] = $this->urlGenerator->generate(
            RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
            [
                RouteObjectInterface::ROUTE_OBJECT => $data,
            ]
        );

        return $normalized;
    }
}
