<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Importer;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use RZ\Roadiz\CoreBundle\Entity\Attribute;
use RZ\Roadiz\CoreBundle\Serializer\ObjectConstructor\TypedObjectConstructorInterface;

final class AttributeImporter implements EntityImporterInterface
{
    public function __construct(private readonly SerializerInterface $serializer)
    {
    }

    public function supports(string $entityClass): bool
    {
        return Attribute::class === $entityClass || $entityClass === 'array<'.Attribute::class.'>';
    }

    public function import(string $serializedData): bool
    {
        $this->serializer->deserialize(
            $serializedData,
            'array<'.Attribute::class.'>',
            'json',
            DeserializationContext::create()
                ->setAttribute(TypedObjectConstructorInterface::PERSIST_NEW_OBJECTS, true)
                ->setAttribute(TypedObjectConstructorInterface::FLUSH_NEW_OBJECTS, true)
        );

        return true;
    }
}
