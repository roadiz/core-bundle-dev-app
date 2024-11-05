<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Importer;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Serializer\ObjectConstructor\TagObjectConstructor;
use RZ\Roadiz\CoreBundle\Serializer\ObjectConstructor\TypedObjectConstructorInterface;

class TagsImporter implements EntityImporterInterface
{
    protected SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function supports(string $entityClass): bool
    {
        return Tag::class === $entityClass;
    }

    public function import(string $serializedData): bool
    {
        $this->serializer->deserialize(
            $serializedData,
            Tag::class,
            'json',
            DeserializationContext::create()
                ->setAttribute(TypedObjectConstructorInterface::PERSIST_NEW_OBJECTS, true)
                ->setAttribute(TypedObjectConstructorInterface::FLUSH_NEW_OBJECTS, true)
                ->setAttribute(TagObjectConstructor::EXCEPTION_ON_EXISTING_TAG, true)
        );

        return true;
    }
}
