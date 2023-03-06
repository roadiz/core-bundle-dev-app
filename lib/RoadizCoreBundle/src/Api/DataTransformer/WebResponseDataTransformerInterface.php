<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\DataTransformer;

use RZ\Roadiz\CoreBundle\Api\Model\WebResponseInterface;

interface WebResponseDataTransformerInterface
{
    /**
     * Transforms the given object to something else, usually another object.
     * This must return the original object if no transformations have been done.
     *
     * @param object $object
     * @param string $to
     * @param array $context
     * @return WebResponseInterface|null
     */
    public function transform($object, string $to, array $context = []): ?WebResponseInterface;

    /**
     * Checks whether the transformation is supported for a given data and context.
     *
     * @param object|array $data object on normalize / array on denormalize
     */
    public function supportsTransformation($data, string $to, array $context = []): bool;
}
