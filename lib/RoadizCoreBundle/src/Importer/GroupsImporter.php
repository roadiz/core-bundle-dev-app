<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Importer;

use RZ\Roadiz\CoreBundle\Entity\Group;
use RZ\Roadiz\CoreBundle\Serializer\Normalizer\GroupNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class GroupsImporter implements EntityImporterInterface
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    #[\Override]
    public function supports(string $entityClass): bool
    {
        return Group::class === $entityClass;
    }

    #[\Override]
    public function import(string $serializedData): bool
    {
        $this->serializer->deserialize(
            $serializedData,
            Group::class.'[]',
            'json',
            [
                'groups' => ['group:import'],
                GroupNormalizer::PERSIST_NEW_ENTITIES => true,
            ]
        );

        return true;
    }
}
