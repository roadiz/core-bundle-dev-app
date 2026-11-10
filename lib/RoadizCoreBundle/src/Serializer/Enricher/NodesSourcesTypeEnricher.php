<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Enricher;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * Enforce the JSON-LD "@type" key with the node-type name for NodesSources entities.
 */
#[AsTaggedItem(priority: -10)]
final readonly class NodesSourcesTypeEnricher implements NormalizationEnricherInterface
{
    #[\Override]
    public function supports(object $data, ?string $format, array $context): bool
    {
        return 'jsonld' === $format && $data instanceof NodesSources;
    }

    /**
     * @param NodesSources $data
     */
    #[\Override]
    public function enrich(array $normalized, object $data, ?string $format, array $context): array
    {
        $normalized['@type'] = $data->getNodeTypeName();

        return $normalized;
    }
}
