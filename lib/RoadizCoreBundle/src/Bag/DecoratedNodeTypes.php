<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Bag;

use Doctrine\DBAL\Driver\PDO\PDOException;
use http\Exception\RuntimeException;
use RZ\Roadiz\Bag\LazyParameterBag;
use RZ\Roadiz\Contracts\NodeType\NodeTypeResolverInterface;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Repository\NodeTypeDecoratorRepository;

/**
 * @method NodeType|null get(string $key, $default = null)
 */
final class DecoratedNodeTypes extends LazyParameterBag implements NodeTypeResolverInterface
{
    public function __construct(
        private readonly NodeTypes $nodeTypesBag,
        private readonly NodeTypeDecoratorRepository $repository,
    ) {
        parent::__construct();
    }

    protected function populateParameters(): void
    {
        $nodeTypes = $this->nodeTypesBag->all();
        foreach ($nodeTypes as $nodeType) {
            $decoratedNodeType = clone $nodeType;
            try {
                $nodeTypeDecorators = $this->repository->findByNodeType($decoratedNodeType);
                foreach ($nodeTypeDecorators as $nodeTypeDecorator) {
                    $nodeTypeDecorator->applyOn($decoratedNodeType);
                }
                $this->parameters[$decoratedNodeType->getName()] = $decoratedNodeType;
                $this->parameters[$decoratedNodeType->getSourceEntityFullQualifiedClassName()] = $decoratedNodeType;
            } catch (PDOException $e) {
                throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
            }
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

    /**
     * @return array<int, NodeType>
     */
    public function allPublishable(bool $publishable = true): array
    {
        return array_values(array_filter($this->all(), function (NodeType $nodeType) use ($publishable) {
            return $nodeType->isPublishable() === $publishable;
        }));
    }
}
