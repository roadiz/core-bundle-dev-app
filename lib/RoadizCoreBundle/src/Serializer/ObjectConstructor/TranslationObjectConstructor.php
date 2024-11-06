<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\ObjectConstructor;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\ObjectConstructionException;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;

final class TranslationObjectConstructor extends AbstractTypedObjectConstructor
{
    public function supports(string $className, array $data): bool
    {
        return \is_subclass_of($className, TranslationInterface::class)
            && array_key_exists('locale', $data);
    }

    protected function findObject(mixed $data, DeserializationContext $context): ?object
    {
        if (null === $data['locale'] || '' === $data['locale']) {
            throw new ObjectConstructionException('Translation locale can not be empty');
        }

        return $this->entityManager
            ->getRepository(TranslationInterface::class)
            ->findOneByLocale($data['locale']);
    }

    protected function fillIdentifier(object $object, array $data): void
    {
        if ($object instanceof TranslationInterface) {
            $object->setLocale($data['locale']);
            $object->setName($data['locale']);
        }
    }
}
