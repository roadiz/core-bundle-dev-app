<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\ObjectConstructor;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\ObjectConstructionException;
use RZ\Roadiz\CoreBundle\Exception\EntityAlreadyExistsException;
use RZ\Roadiz\CoreBundle\Model\AttributeInterface;

final class AttributeObjectConstructor extends AbstractTypedObjectConstructor
{
    public const EXCEPTION_ON_EXISTING = 'exception_on_existing_attribute';

    /**
     * @inheritDoc
     */
    public function supports(string $className, array $data): bool
    {
        return (
            \is_subclass_of($className, AttributeInterface::class)
        ) && \array_key_exists('code', $data);
    }

    /**
     * @inheritDoc
     */
    protected function findObject(mixed $data, DeserializationContext $context): ?object
    {
        if (empty($data['code'])) {
            throw new ObjectConstructionException('Attribute code can not be empty');
        }
        $tag = $this->entityManager
            ->getRepository(AttributeInterface::class)
            ->findOneByCode($data['code']);

        if (
            null !== $tag &&
            $context->hasAttribute(self::EXCEPTION_ON_EXISTING) &&
            true === $context->hasAttribute(self::EXCEPTION_ON_EXISTING)
        ) {
            throw new EntityAlreadyExistsException('Attribute already exists in database.');
        }

        return $tag;
    }

    protected function fillIdentifier(object $object, array $data): void
    {
        if ($object instanceof AttributeInterface) {
            $object->setCode($data['code']);
        }
    }
}
