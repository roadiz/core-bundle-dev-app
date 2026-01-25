<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\DataTransformer;

use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

final readonly class NodeTypeTransformer implements DataTransformerInterface
{
    public function __construct(
        private NodeTypes $nodeTypesBag,
    ) {
    }

    /**
     * @param NodeType|null $value
     */
    #[\Override]
    public function transform(mixed $value): string
    {
        if (!$value instanceof NodeType) {
            return '';
        }

        return $value->getName();
    }

    #[\Override]
    public function reverseTransform(mixed $value): ?NodeType
    {
        if (!$value || !is_string($value)) {
            return null;
        }

        $nodeType = $this->nodeTypesBag->get($value);

        if (null === $nodeType) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf('A node-type with id "%s" does not exist!', $value));
        }

        return $nodeType;
    }
}
