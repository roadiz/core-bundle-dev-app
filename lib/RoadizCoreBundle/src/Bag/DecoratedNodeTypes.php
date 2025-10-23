<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Bag;

use Doctrine\DBAL\Driver\Exception;
use RZ\Roadiz\Bag\LazyParameterBag;
use RZ\Roadiz\Contracts\NodeType\NodeTypeClassLocatorInterface;
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
        private readonly NodeTypeClassLocatorInterface $nodeTypeClassLocator,
    ) {
        parent::__construct();
    }

    #[\Override]
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
            } catch (Exception) {
            }
            $this->parameters[$decoratedNodeType->getName()] = $decoratedNodeType;
            $this->parameters[$this->nodeTypeClassLocator->getSourceEntityFullQualifiedClassName($decoratedNodeType)] = $decoratedNodeType;
        }

        $this->ready = true;
    }

    /**
     * @return array<int, NodeType>
     */
    #[\Override]
    public function all(?string $key = null): array
    {
        return array_values(array_unique(parent::all($key)));
    }

    #[\ReturnTypeWillChange]
    #[\Override]
    public function count(): int
    {
        return count($this->all());
    }

    /**
     * @return array<int, NodeType>
     */
    public function allVisible(bool $visible = true): array
    {
        return array_values(array_filter($this->all(), fn (NodeType $nodeType) => $nodeType->isVisible() === $visible));
    }

    /**
     * @return array<int, NodeType>
     */
    public function allReachable(bool $reachable = true): array
    {
        return array_values(array_filter($this->all(), fn (NodeType $nodeType) => $nodeType->isReachable() === $reachable));
    }

    /**
     * @return array<int, NodeType>
     */
    public function allPublishable(bool $publishable = true): array
    {
        return array_values(array_filter($this->all(), fn (NodeType $nodeType) => $nodeType->isPublishable() === $publishable));
    }

    /**
     * @return array<int, NodeType>
     */
    public function allHighlighted(bool $highlighted = true): array
    {
        return array_values(array_filter($this->all(), fn (NodeType $nodeType) => $nodeType->isHighlighted() === $highlighted));
    }

    /**
     * @return array<int, NodeType>
     */
    public function allSorted(?string $sort = 'ASC'): array
    {
        $nodeTypes = $this->all();

        usort($nodeTypes, function (NodeType $a, NodeType $b) use ($sort) {
            if ('DESC' !== $sort) {
                return strcmp($a->getName(), $b->getName());
            } else {
                return strcmp($b->getName(), $a->getName());
            }
        });

        return $nodeTypes;
    }
}
