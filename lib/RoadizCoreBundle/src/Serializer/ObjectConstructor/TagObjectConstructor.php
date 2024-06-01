<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\ObjectConstructor;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\ObjectConstructionException;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Exception\EntityAlreadyExistsException;

final class TagObjectConstructor extends AbstractTypedObjectConstructor
{
    public const EXCEPTION_ON_EXISTING_TAG = 'exception_on_existing_tag';

    /**
     * @inheritDoc
     */
    public function supports(string $className, array $data): bool
    {
        return $className === Tag::class && (
            \array_key_exists('tagName', $data) ||
            \array_key_exists('tag_name', $data)
        );
    }

    /**
     * @inheritDoc
     */
    protected function findObject(mixed $data, DeserializationContext $context): ?object
    {
        if (empty($data['tagName']) && empty($data['tag_name'])) {
            throw new ObjectConstructionException('Tag name can not be empty');
        }
        $tag = $this->entityManager
            ->getRepository(Tag::class)
            ->findOneByTagName($data['tagName'] ?? $data['tag_name']);

        if (
            null !== $tag &&
            $context->hasAttribute(static::EXCEPTION_ON_EXISTING_TAG) &&
            true === $context->hasAttribute(static::EXCEPTION_ON_EXISTING_TAG)
        ) {
            throw new EntityAlreadyExistsException('Tag already exists in database.');
        }

        return $tag;
    }

    protected function fillIdentifier(object $object, array $data): void
    {
        if ($object instanceof Tag) {
            $object->setTagName($data['tagName'] ?? $data['tag_name']);
        }
    }
}
