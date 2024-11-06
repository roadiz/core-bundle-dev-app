<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\ObjectConstructor;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\ObjectConstructionException;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\CoreBundle\Entity\NodeType;

final class NodeTypeObjectConstructor extends AbstractTypedObjectConstructor
{
    public function supports(string $className, array $data): bool
    {
        return \is_subclass_of($className, NodeTypeInterface::class)
            && array_key_exists('name', $data);
    }

    protected function findObject(mixed $data, DeserializationContext $context): ?object
    {
        if (null === $data['name'] || '' === $data['name']) {
            throw new ObjectConstructionException('NodeType name can not be empty');
        }

        return $this->entityManager
            ->getRepository(NodeType::class)
            ->findOneByName($data['name']);
    }

    protected function fillIdentifier(object $object, array $data): void
    {
        if ($object instanceof NodeType) {
            $object->setName($data['name']);
        }
    }
}
