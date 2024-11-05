<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\ObjectConstructor;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\ObjectConstructionException;
use RZ\Roadiz\CoreBundle\Entity\Group;

final class GroupObjectConstructor extends AbstractTypedObjectConstructor
{
    public function supports(string $className, array $data): bool
    {
        return Group::class === $className && array_key_exists('name', $data);
    }

    protected function findObject(mixed $data, DeserializationContext $context): ?object
    {
        if (null === $data['name'] || '' === $data['name']) {
            throw new ObjectConstructionException('Group name can not be empty');
        }

        return $this->entityManager->getRepository(Group::class)->findOneByName($data['name']);
    }

    protected function fillIdentifier(object $object, array $data): void
    {
        trigger_error('Cannot call fillIdentifier on Group', E_USER_WARNING);
    }

    protected function canBeFlushed(): bool
    {
        return false;
    }
}
