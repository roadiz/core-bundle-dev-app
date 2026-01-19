<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\DataTransformer;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\AttributeGroup;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

final readonly class AttributeGroupTransformer implements DataTransformerInterface
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }

    /**
     * @param AttributeGroup|null $value
     */
    #[\Override]
    public function transform(mixed $value): int|string
    {
        if (!$value instanceof AttributeGroup) {
            return '';
        }

        return $value->getId() ?? throw new TransformationFailedException('AttributeGroup has no ID.');
    }

    #[\Override]
    public function reverseTransform(mixed $value): ?AttributeGroup
    {
        if (!$value) {
            return null;
        }

        $attributeGroup = $this->managerRegistry
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
