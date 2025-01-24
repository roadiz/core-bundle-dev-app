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
    public function transform(mixed $value): int|string
    {
        if (!$value instanceof NodeType) {
            return '';
        }

        return $value->getId();
    }

    public function reverseTransform(mixed $value): ?NodeType
    {
        if (!$value) {
            return null;
        }

        if (is_string($value)) {
            $nodeType = $this->nodeTypesBag->get($value);
        } else {
            $nodeType = $this->nodeTypesBag->getById($value);
        }

        if (null === $nodeType) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf('A node-type with id "%s" does not exist!', $value));
        }

        return $nodeType;
    }
}
