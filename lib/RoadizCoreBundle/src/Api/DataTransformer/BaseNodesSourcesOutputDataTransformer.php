<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\DataTransformer;

use RZ\Roadiz\CoreBundle\Api\Dto\NodesSourcesOutput;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;

/**
 * @deprecated
 */
class BaseNodesSourcesOutputDataTransformer extends NodesSourcesOutputDataTransformer
{
    /**
     * @inheritDoc
     */
    public function transform($data, string $to, array $context = []): object
    {
        if (!$data instanceof NodesSources) {
            throw new \InvalidArgumentException('Data to transform must be instance of ' . NodesSources::class);
        }
        $output = new NodesSourcesOutput();

        return $this->transformNodesSources($output, $data, $context);
    }

    /**
     * @inheritDoc
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return NodesSourcesOutput::class === $to && $data instanceof NodesSources;
    }
}
