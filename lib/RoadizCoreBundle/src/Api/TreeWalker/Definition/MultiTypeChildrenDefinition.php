<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\TreeWalker\Definition;

use RZ\Roadiz\CoreBundle\Api\TreeWalker\NodeSourceWalkerContext;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\TreeWalker\Definition\ContextualDefinitionTrait;
use RZ\TreeWalker\WalkerContextInterface;

final readonly class MultiTypeChildrenDefinition
{
    use ContextualDefinitionTrait;
    use NodeSourceDefinitionTrait;

    /**
     * @param array<string> $types
     */
    public function __construct(
        private WalkerContextInterface $context,
        private array $types,
        private bool $onlyVisible = true,
    ) {
    }

    /**
     * @return array<NodeType> $nodeTypes
     */
    #[\Override]
    protected function getNodeTypes(NodeTypes $nodeTypesBag): array
    {
        return array_values(array_filter(array_map(fn (string $singleType) => $nodeTypesBag->get($singleType), $this->types)));
    }

    /**
     * @return array<NodesSources>
     */
    public function __invoke(NodesSources $source): array
    {
        if (!($this->context instanceof NodeSourceWalkerContext)) {
            throw new \InvalidArgumentException('Context should be instance of '.NodeSourceWalkerContext::class);
        }

        $this->context->getStopwatch()->start(self::class);
        $queryBuilder = $this->getQueryBuilder($source, $this->onlyVisible);
        $this->context->getStopwatch()->stop(self::class);

        return $queryBuilder->getQuery()->getResult();
    }
}
