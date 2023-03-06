<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\ObjectConstructor;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\ObjectConstructionException;
use RZ\Roadiz\CoreBundle\Entity\Translation;

class TranslationObjectConstructor extends AbstractTypedObjectConstructor
{
    /**
     * @inheritDoc
     */
    public function supports(string $className, array $data): bool
    {
        return $className === Translation::class && array_key_exists('locale', $data);
    }

    /**
     * @inheritDoc
     */
    protected function findObject($data, DeserializationContext $context): ?object
    {
        if (null === $data['locale'] || $data['locale'] === '') {
            throw new ObjectConstructionException('Translation locale can not be empty');
        }

        return $this->entityManager
            ->getRepository(Translation::class)
            ->findOneByLocale($data['locale']);
    }

    protected function fillIdentifier(object $object, array $data): void
    {
        if ($object instanceof Translation) {
            $object->setLocale($data['locale']);
            $object->setName($data['locale']);
        }
    }
}
