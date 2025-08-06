<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;

final class NodesSourcesPathNormalizer extends AbstractPathNormalizer
{
    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $normalized = $this->decorated->normalize($data, $format, $context);
        if (
            $data instanceof NodesSources
            && $data->isReachable()
            && \is_array($normalized)
            && !isset($normalized['url'])
            && isset($context['groups'])
            && \in_array('urls', $context['groups'], true)
        ) {
            $this->stopwatch->start('normalizeNodesSourcesUrl', 'serializer');
            $normalized['url'] = $this->urlGenerator->generate(
                RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                [
                    RouteObjectInterface::ROUTE_OBJECT => $data,
                ]
            );
            $this->stopwatch->stop('normalizeNodesSourcesUrl');
        }

        return $normalized;
    }
}
