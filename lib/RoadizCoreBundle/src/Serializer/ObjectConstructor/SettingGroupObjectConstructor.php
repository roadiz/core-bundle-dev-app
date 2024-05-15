<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\ObjectConstructor;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\ObjectConstructionException;
use RZ\Roadiz\CoreBundle\Entity\SettingGroup;

final class SettingGroupObjectConstructor extends AbstractTypedObjectConstructor
{
    /**
     * @inheritDoc
     */
    public function supports(string $className, array $data): bool
    {
        return $className === SettingGroup::class && array_key_exists('name', $data);
    }

    /**
     * @inheritDoc
     */
    protected function findObject(mixed $data, DeserializationContext $context): ?object
    {
        if (null === $data['name'] || $data['name'] === '') {
            throw new ObjectConstructionException('SettingGroup name can not be empty');
        }
        return $this->entityManager
            ->getRepository(SettingGroup::class)
            ->findOneByName($data['name']);
    }

    protected function fillIdentifier(object $object, array $data): void
    {
        if ($object instanceof SettingGroup) {
            $object->setName($data['name']);
        }
    }
}
