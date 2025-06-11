<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Importer;

use RZ\Roadiz\CoreBundle\Entity\Attribute;
use RZ\Roadiz\CoreBundle\Serializer\Normalizer\AttributeNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class AttributeImporter implements EntityImporterInterface
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    #[\Override]
    public function supports(string $entityClass): bool
    {
        return Attribute::class === $entityClass;
    }

    #[\Override]
    public function import(string $serializedData): bool
    {
        $this->serializer->deserialize(
            $serializedData,
            Attribute::class.'[]',
            'json',
            [
                'groups' => ['attribute:import'],
                AttributeNormalizer::PERSIST_NEW_ENTITIES => true,
            ]
        );

        return true;
    }
}
