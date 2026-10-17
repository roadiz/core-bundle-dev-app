<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\TreeWalker;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use Doctrine\Common\Collections\Collection;
use RZ\TreeWalker\WalkerInterface;
use Symfony\Component\TypeInfo\Type;

/**
 * Walker children are not addressable resources: they must not get random skolem IRIs (@id),
 * so that the same tree always serializes to the same JSON.
 *
 * genId alone is not enough: PropertyInfo cannot resolve the `Collection<int, static>` type of
 * AbstractWalker::$children, and API Platform then drops genId from the serialization context.
 * Children are typed as a collection of the walker's own class instead.
 */
final readonly class WalkerChildrenPropertyMetadataFactory implements PropertyMetadataFactoryInterface
{
    public function __construct(
        private PropertyMetadataFactoryInterface $decorated,
    ) {
    }

    #[\Override]
    public function create(string $resourceClass, string $property, array $options = []): ApiProperty
    {
        $propertyMetadata = $this->decorated->create($resourceClass, $property, $options);
        if ('children' !== $property || !is_a($resourceClass, WalkerInterface::class, true)) {
            return $propertyMetadata;
        }

        return $propertyMetadata->withGenId(false)->withNativeType(
            Type::collection(Type::object(Collection::class), Type::object($resourceClass), Type::int())
        );
    }
}
