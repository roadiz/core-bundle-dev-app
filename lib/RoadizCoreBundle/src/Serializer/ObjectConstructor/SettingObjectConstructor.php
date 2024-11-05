<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\ObjectConstructor;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\ObjectConstructionException;
use RZ\Roadiz\CoreBundle\Entity\Setting;

final class SettingObjectConstructor extends AbstractTypedObjectConstructor
{
    public function supports(string $className, array $data): bool
    {
        return Setting::class === $className && array_key_exists('name', $data);
    }

    protected function findObject(mixed $data, DeserializationContext $context): ?object
    {
        if (null === $data['name'] || '' === $data['name']) {
            throw new ObjectConstructionException('Setting name can not be empty');
        }

        return $this->entityManager->getRepository(Setting::class)->findOneByName($data['name']);
    }

    protected function fillIdentifier(object $object, array $data): void
    {
        if ($object instanceof Setting) {
            $object->setName($data['name']);
        }
    }
}
