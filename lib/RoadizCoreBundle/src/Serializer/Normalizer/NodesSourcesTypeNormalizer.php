<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;

final class NodesSourcesTypeNormalizer extends AbstractPathNormalizer
{
    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        if (!$data instanceof NodesSources) {
            return $this->decorated->normalize($data, $format, $context);
        }

        /*
         * Enforce @type field for NodesSources entities
         */
        $normalized = $this->decorated->normalize($data, $format, $context);
        if (is_array($normalized)) {
            $normalized['@type'] = $data->getNodeTypeName();
        }

        return $normalized;
    }
}
