<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\ObjectConstructor;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\ObjectConstructionException;
use RZ\Roadiz\CoreBundle\Entity\Role;

final class RoleObjectConstructor extends AbstractTypedObjectConstructor
{
    public function supports(string $className, array $data): bool
    {
        return Role::class === $className && array_key_exists('name', $data);
    }

    protected function findObject(mixed $data, DeserializationContext $context): ?object
    {
        if (null === $data['name'] || '' === $data['name']) {
            throw new ObjectConstructionException('Role name can not be empty');
        }

        return $this->entityManager
            ->getRepository(Role::class)
            ->findOneByName($data['name']);
    }

    protected function fillIdentifier(object $object, array $data): void
    {
        if ($object instanceof Role) {
            $object->setRole($data['name']);
        }
    }
}
