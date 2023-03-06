<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Importer;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use RZ\Roadiz\CoreBundle\Entity\Setting;
use RZ\Roadiz\CoreBundle\Serializer\ObjectConstructor\TypedObjectConstructorInterface;

class SettingsImporter implements EntityImporterInterface
{
    private ManagerRegistry $managerRegistry;
    private SerializerInterface $serializer;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param SerializerInterface $serializer
     */
    public function __construct(ManagerRegistry $managerRegistry, SerializerInterface $serializer)
    {
        $this->managerRegistry = $managerRegistry;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function supports(string $entityClass): bool
    {
        return $entityClass === Setting::class;
    }

    /**
     * @inheritDoc
     */
    public function import(string $serializedData): bool
    {
        $settings = $this->serializer->deserialize(
            $serializedData,
            'array<' . Setting::class . '>',
            'json',
            DeserializationContext::create()
                ->setAttribute(TypedObjectConstructorInterface::PERSIST_NEW_OBJECTS, true)
                ->setAttribute(TypedObjectConstructorInterface::FLUSH_NEW_OBJECTS, true)
        );

        $manager = $this->managerRegistry->getManagerForClass(Setting::class);
        if ($manager instanceof EntityManagerInterface) {
            // Clear result cache
            $cacheDriver = $manager->getConfiguration()->getResultCacheImpl();
            if ($cacheDriver instanceof CacheProvider) {
                $cacheDriver->deleteAll();
            }
        }

        return true;
    }
}
