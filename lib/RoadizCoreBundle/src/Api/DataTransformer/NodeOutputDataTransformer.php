<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use RZ\Roadiz\CoreBundle\Api\Dto\NodeOutput;
use RZ\Roadiz\CoreBundle\Entity\Node;

/**
 * @deprecated Use serialization groups or custom normalizer
 */
class NodeOutputDataTransformer implements DataTransformerInterface
{
    /**
     * @inheritDoc
     */
    public function transform($data, string $to, array $context = []): object
    {
        if (!$data instanceof Node) {
            throw new \InvalidArgumentException('Data to transform must be instance of ' . Node::class);
        }
        $output = new NodeOutput();
        $output->nodeName = $data->getNodeName();
        $output->visible = $data->isVisible();
        $output->position = $data->getPosition();
        $output->tags = $data->getTags()->toArray();
        return $output;
    }

    /**
     * @inheritDoc
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return NodeOutput::class === $to && $data instanceof Node;
    }
}
