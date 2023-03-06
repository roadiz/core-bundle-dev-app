<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class NodesSourcesPathNormalizer extends AbstractPathNormalizer
{
     /**
     * @param mixed $object
     * @param string|null $format
     * @param array $context
     * @return array|\ArrayObject|bool|float|int|mixed|string|null
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function normalize($object, $format = null, array $context = [])
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
            $data['url'] = $this->urlGenerator->generate(
                RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                [
                    RouteObjectInterface::ROUTE_OBJECT => $object
                ],
                UrlGeneratorInterface::ABSOLUTE_PATH
            );
        }
        return $data;
    }
}
