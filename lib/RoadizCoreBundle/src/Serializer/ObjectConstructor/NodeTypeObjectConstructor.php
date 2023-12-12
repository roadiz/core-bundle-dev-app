<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\ObjectConstructor;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\ObjectConstructionException;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use RZ\Roadiz\CoreBundle\Entity\NodeType;

class NodeTypeObjectConstructor extends AbstractTypedObjectConstructor
{
    /**
     * @inheritDoc
     */
    public function supports(string $className, array $data): bool
    {
        return $className === NodeType::class && array_key_exists('name', $data);
    }

    public function construct(
        DeserializationVisitorInterface $visitor,
        ClassMetadata $metadata,
        $data,
        array $type,
        DeserializationContext $context
    ): ?object {
        $nodeType = parent::construct($visitor, $metadata, $data, $type, $context);

        if ($nodeType instanceof NodeType && \is_array($data) && \array_key_exists('fields', $data)) {
            $nodeType = $this->removeExtraFields($nodeType, $data);
        }

        return $nodeType;
    }

    protected function removeExtraFields(NodeType $nodeType, array $data): NodeType
    {
        $fieldsName = array_map(function ($field) {
            return $field['name'];
        }, $data['fields']);

        foreach ($nodeType->getFields() as $field) {
            if (!\in_array($field->getName(), $fieldsName)) {
                $nodeType->getFields()->removeElement($field);
                $field->setNodeType(null);
            }
        }

        return $nodeType;
    }

    /**
     * @inheritDoc
     */
    protected function findObject($data, DeserializationContext $context): ?object
    {
        if (null === $data['name'] || $data['name'] === '') {
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
