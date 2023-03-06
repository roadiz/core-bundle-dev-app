<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use RZ\Roadiz\UserBundle\Api\Dto\VoidOutput;

final class VoidOutputDataTransformer implements DataTransformerInterface
{
    /**
     * @inheritDoc
     */
    public function transform($object, string $to, array $context = []): VoidOutput
    {
        return new VoidOutput();
    }

    /**
     * @inheritDoc
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return $to === VoidOutput::class;
    }
}
