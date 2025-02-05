<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Bag;

use RZ\Roadiz\Bag\LazyParameterBag;
use RZ\Roadiz\Contracts\NodeType\NodeTypeResolverInterface;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Repository\NodeTypeRepositoryInterface;

/**
 * @method NodeType|null get(string $key, $default = null)
 */
final class NodeTypes extends LazyParameterBag implements NodeTypeResolverInterface
{
    public function __construct(private readonly NodeTypeRepositoryInterface $repository)
    {
        parent::__construct();
    }

    protected function populateParameters(): void
    {
        $nodeTypes = $this->repository->findAll();
        $this->parameters = [];
        foreach ($nodeTypes as $nodeType) {
            $this->parameters[$nodeType->getName()] = $nodeType;
            $this->parameters[$nodeType->getSourceEntityFullQualifiedClassName()] = $nodeType;
        }

        $this->ready = true;
    }

    /**
     * @return array<int, NodeType>
     */
    public function all(?string $key = null): array
    {
        return array_values(array_unique(parent::all($key)));
    }

    #[\ReturnTypeWillChange]
    public function count(): int
    {
        return count($this->all());
    }

    /**
     * @internal this may change in future Roadiz versions when NodeTypes will be static
     */
    public function getById(int $id): ?NodeType
    {
        return array_values(array_filter($this->all(), function (NodeType $nodeType) use ($id) {
            return $nodeType->getId() === $id;
        }))[0] ?? null;
    }

    /**
     * @return array<int, NodeType>
     */
    public function allVisible(bool $visible = true): array
    {
        return array_values(array_filter($this->all(), function (NodeType $nodeType) use ($visible) {
            return $nodeType->isVisible() === $visible;
        }));
    }

    /**
     * @return array<int, NodeType>
     */
    public function allReachable(bool $reachable = true): array
    {
        return array_values(array_filter($this->all(), function (NodeType $nodeType) use ($reachable) {
            return $nodeType->isReachable() === $reachable;
        }));
    }
}
