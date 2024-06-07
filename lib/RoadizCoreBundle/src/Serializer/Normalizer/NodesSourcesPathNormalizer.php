<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;

final class NodesSourcesPathNormalizer extends AbstractPathNormalizer
{
     /**
     * @param mixed $object
     * @param string|null $format
     * @param array $context
     * @return array|\ArrayObject|bool|float|int|mixed|string|null
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): mixed
    {
        $data = $this->decorated->normalize($object, $format, $context);
        if (
            $object instanceof NodesSources &&
            $object->isReachable() &&
            \is_array($data) &&
            !isset($data['url']) &&
            isset($context['groups']) &&
            \in_array('urls', $context['groups'], true)
        ) {
            $this->stopwatch->start('normalizeNodesSourcesUrl', 'serializer');
            $data['url'] = $this->urlGenerator->generate(
                RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                [
                    RouteObjectInterface::ROUTE_OBJECT => $object
                ]
            );
            $this->stopwatch->stop('normalizeNodesSourcesUrl');
        }
        return $data;
    }
}
