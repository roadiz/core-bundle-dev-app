<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\DataTransformer;

use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\AttributeGroup;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class AttributeGroupTransformer.
 */
class AttributeGroupTransformer implements DataTransformerInterface
{
    private ObjectManager $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param AttributeGroup|null $value
     */
    public function transform(mixed $value): int|string
    {
        if (!$value instanceof AttributeGroup) {
            return '';
        }

        return $value->getId();
    }

    public function reverseTransform(mixed $value): ?AttributeGroup
    {
        if (!$value) {
            return null;
        }

        $attributeGroup = $this->manager
            ->getRepository(AttributeGroup::class)
            ->find($value)
        ;

        if (null === $attributeGroup) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf('A attribute-group with id "%s" does not exist!', $value));
        }

        return $attributeGroup;
    }
}
