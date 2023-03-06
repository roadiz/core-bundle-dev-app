<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\ObjectConstructor;

use Doctrine\Persistence\ObjectManager;
use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;

class ChainDoctrineObjectConstructor implements ObjectConstructorInterface
{
    protected ?ObjectManager $entityManager;
    /**
     * @var array<TypedObjectConstructorInterface>
     */
    protected array $typedObjectConstructors;
    protected ObjectConstructorInterface $fallbackConstructor;

    /**
     * @param ObjectManager|null $entityManager
     * @param ObjectConstructorInterface $fallbackConstructor
     * @param array $typedObjectConstructors
     */
    public function __construct(
        ?ObjectManager $entityManager,
        ObjectConstructorInterface $fallbackConstructor,
        array $typedObjectConstructors
    ) {
        $this->entityManager = $entityManager;
        $this->typedObjectConstructors = $typedObjectConstructors;
        $this->fallbackConstructor = $fallbackConstructor;
    }

    /**
     * @param DeserializationVisitorInterface $visitor
     * @param ClassMetadata $metadata
     * @param PersistableInterface|array<PersistableInterface> $data
     * @param array $type
     * @param DeserializationContext $context
     * @return object|null
     */
    public function construct(
        DeserializationVisitorInterface $visitor,
        ClassMetadata $metadata,
        $data,
        array $type,
        DeserializationContext $context
    ): ?object {
        if (null === $this->entityManager) {
            // No ObjectManager found, proceed with normal deserialization
            return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
        }

        // Locate possible ClassMetadata
        $classMetadataFactory = $this->entityManager->getMetadataFactory();
        try {
            $doctrineMetadata = $classMetadataFactory->getMetadataFor($metadata->name);
            if ($doctrineMetadata->getName() !== $metadata->name) {
                /*
                 * Doctrine resolveTargetEntity has found an alternative class
                 */
                $metadata = new ClassMetadata($doctrineMetadata->getName());
            }
        } catch (\Doctrine\ORM\Mapping\MappingException $e) {
            // Object class is not a valid doctrine entity
        }

        if ($classMetadataFactory->isTransient($metadata->name)) {
            // No ClassMetadata found, proceed with normal deserialization
            return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
        }

        // Managed entity, check for proxy load
        if (!\is_array($data)) {
            // Single identifier, load proxy
            return $this->entityManager->getReference($metadata->name, $data);
        }

        /** @var TypedObjectConstructorInterface $typedObjectConstructor */
        foreach ($this->typedObjectConstructors as $typedObjectConstructor) {
            if ($typedObjectConstructor->supports($metadata->name, $data)) {
                return $typedObjectConstructor->construct(
                    $visitor,
                    $metadata,
                    $data,
                    $type,
                    $context
                );
            }
        }

        // PHPStan need to explicit classname
        /** @var class-string<PersistableInterface> $className */
        $className = $metadata->name;

        // Fallback to default constructor if missing identifier(s)
        $classMetadata = $this->entityManager->getClassMetadata($className);
        $identifierList = [];

        foreach ($classMetadata->getIdentifierFieldNames() as $name) {
            if (
                isset($metadata->propertyMetadata[$name]) &&
                isset($metadata->propertyMetadata[$name]->serializedName)
            ) {
                $dataName = $metadata->propertyMetadata[$name]->serializedName;
            } else {
                $dataName = $name;
            }

            if (!array_key_exists($dataName, $data)) {
                return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
            }
            $identifierList[$name] = $data[$dataName];
        }

        // Entity update, load it from database
        $object = $this->entityManager->find($className, $identifierList);

        if (null === $object) {
            return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
        }

        $this->entityManager->initializeObject($object);

        return $object;
    }
}
