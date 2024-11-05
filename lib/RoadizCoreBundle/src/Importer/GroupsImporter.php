<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Importer;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use RZ\Roadiz\CoreBundle\Entity\Group;
use RZ\Roadiz\CoreBundle\Serializer\ObjectConstructor\TypedObjectConstructorInterface;

class GroupsImporter implements EntityImporterInterface
{
    protected SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function supports(string $entityClass): bool
    {
        return Group::class === $entityClass;
    }

    public function import(string $serializedData): bool
    {
        $this->serializer->deserialize(
            $serializedData,
            'array<'.Group::class.'>',
            'json',
            DeserializationContext::create()
                ->setAttribute(TypedObjectConstructorInterface::PERSIST_NEW_OBJECTS, true)
                ->setAttribute(TypedObjectConstructorInterface::FLUSH_NEW_OBJECTS, true)
        );

        return true;
    }
}
