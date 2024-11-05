<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\ObjectConstructor;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\ObjectConstructionException;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Repository\NodeRepository;

final class NodeObjectConstructor extends AbstractTypedObjectConstructor
{
    public function supports(string $className, array $data): bool
    {
        return Node::class === $className && array_key_exists('nodeName', $data);
    }

    protected function findObject(mixed $data, DeserializationContext $context): ?object
    {
        if (empty($data['nodeName']) && empty($data['node_name'])) {
            throw new ObjectConstructionException('Node name can not be empty');
        }
        /** @var NodeRepository $nodeRepository */
        $nodeRepository = $this->entityManager
            ->getRepository(Node::class)
            ->setDisplayingAllNodesStatuses(true);

        return $nodeRepository->findOneByNodeName($data['nodeName'] ?? $data['node_name']);
    }

    protected function fillIdentifier(object $object, array $data): void
    {
        if ($object instanceof Node) {
            $object->setNodeName($data['nodeName'] ?? $data['node_name']);
        }
    }
}
