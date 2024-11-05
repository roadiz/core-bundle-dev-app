<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\ObjectConstructor;

use Doctrine\Persistence\ObjectManager;
use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use RZ\Roadiz\CoreBundle\Exception\EntityAlreadyExistsException;

abstract class AbstractTypedObjectConstructor implements TypedObjectConstructorInterface
{
    public function __construct(
        protected readonly ObjectManager $entityManager,
        protected readonly ObjectConstructorInterface $fallbackConstructor,
    ) {
    }

    abstract protected function findObject(mixed $data, DeserializationContext $context): ?object;

    abstract protected function fillIdentifier(object $object, array $data): void;

    protected function canBeFlushed(): bool
    {
        return true;
    }

    public function construct(
        DeserializationVisitorInterface $visitor,
        ClassMetadata $metadata,
        $data,
        array $type,
        DeserializationContext $context,
    ): ?object {
        // Entity update, load it from database
        $object = $this->findObject($data, $context);

        if (
            null !== $object
            && $context->hasAttribute(static::EXCEPTION_ON_EXISTING)
            && true === $context->getAttribute(static::EXCEPTION_ON_EXISTING)
        ) {
            throw new EntityAlreadyExistsException('Object already exists in database.');
        }

        if (null === $object) {
            $object = $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
            if (
                $context->hasAttribute(static::PERSIST_NEW_OBJECTS)
                && true === $context->getAttribute(static::PERSIST_NEW_OBJECTS)
            ) {
                $this->entityManager->persist($object);
            }

            if ($this->canBeFlushed()) {
                /*
                 * If we need to fetch related entities, we can flush light objects with
                 * at least their identifier key filled.
                 */
                $this->fillIdentifier($object, $data);

                if (
                    $context->hasAttribute(static::FLUSH_NEW_OBJECTS)
                    && true === $context->getAttribute(static::FLUSH_NEW_OBJECTS)
                ) {
                    $this->entityManager->flush();
                }
            }
        }

        $this->entityManager->initializeObject($object);

        return $object;
    }
}
