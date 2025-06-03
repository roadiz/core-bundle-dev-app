<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Importer;

use RZ\Roadiz\CoreBundle\Entity\Role;
use RZ\Roadiz\CoreBundle\Serializer\Normalizer\RoleNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class RolesImporter implements EntityImporterInterface
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    #[\Override]
    public function supports(string $entityClass): bool
    {
        return Role::class === $entityClass;
    }

    #[\Override]
    public function import(string $serializedData): bool
    {
        $this->serializer->deserialize(
            $serializedData,
            Role::class.'[]',
            'json',
            [
                'groups' => ['role:import'],
                RoleNormalizer::PERSIST_NEW_ENTITIES => true,
            ]
        );

        return true;
    }
}
