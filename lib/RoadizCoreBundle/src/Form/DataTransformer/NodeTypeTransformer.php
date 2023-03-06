<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\DataTransformer;

use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class NodeTypeTransformer implements DataTransformerInterface
{
    private ObjectManager $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param NodeType|null $value
     * @return int|string
     */
    public function transform(mixed $value): int|string
    {
        if (!$value instanceof NodeType) {
            return '';
        }
        return $value->getId();
    }

    /**
     * @param mixed $value
     * @return null|NodeType
     */
    public function reverseTransform(mixed $value): ?NodeType
    {
        if (!$value) {
            return null;
        }

        $nodeType = $this->manager
            ->getRepository(NodeType::class)
            ->find($value)
        ;

        if (null === $nodeType) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'A node-type with id "%s" does not exist!',
                $value
            ));
        }

        return $nodeType;
    }
}
