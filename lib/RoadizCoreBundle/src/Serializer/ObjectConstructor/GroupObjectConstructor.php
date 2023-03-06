<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\ObjectConstructor;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\ObjectConstructionException;
use RZ\Roadiz\CoreBundle\Entity\Group;

class GroupObjectConstructor extends AbstractTypedObjectConstructor
{
    /**
     * @inheritDoc
     */
    public function supports(string $className, array $data): bool
    {
        return $className === Group::class && array_key_exists('name', $data);
    }

    /**
     * @inheritDoc
     */
    protected function findObject($data, DeserializationContext $context): ?object
    {
        if (null === $data['name'] || $data['name'] === '') {
            throw new ObjectConstructionException('Group name can not be empty');
        }
        return $this->entityManager->getRepository(Group::class)->findOneByName($data['name']);
    }

    protected function fillIdentifier(object $object, array $data): void
    {
        trigger_error('Cannot call fillIdentifier on Group', E_USER_WARNING);
    }

    /**
     * @return bool
     */
    protected function canBeFlushed(): bool
    {
        return false;
    }
}
