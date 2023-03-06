<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\ObjectConstructor;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\ObjectConstructionException;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;

class NodeTypeFieldObjectConstructor extends AbstractTypedObjectConstructor
{
    /**
     * @inheritDoc
     */
    public function supports(string $className, array $data): bool
    {
        return $className === NodeTypeField::class && array_key_exists('name', $data);
    }

    /**
     * @inheritDoc
     */
    protected function findObject($data, DeserializationContext $context): ?object
    {
        if (empty($data['name'])) {
            throw new ObjectConstructionException('NodeTypeField name can not be empty');
        }
        if (empty($data['nodeTypeName']) && empty($data['node_type_name'])) {
            throw new ObjectConstructionException('nodeTypeName is missing to check duplication.');
        }

        $nodeType = $this->entityManager
            ->getRepository(NodeType::class)
            ->findOneByName($data['nodeTypeName'] ?? $data['node_type_name']);

        if (null === $nodeType) {
            /*
             * Do not look for existing fields if node-type does not exist either.
             */
            return null;
        }
        return $this->entityManager
            ->getRepository(NodeTypeField::class)
            ->findOneBy([
                'name' => $data['name'],
                'nodeType' => $nodeType,
            ]);
    }

    protected function fillIdentifier(object $object, array $data): void
    {
        trigger_error('Cannot call fillIdentifier on NodeTypeField', E_USER_WARNING);
    }

    /**
     * @return bool
     */
    protected function canBeFlushed(): bool
    {
        return false;
    }
}
