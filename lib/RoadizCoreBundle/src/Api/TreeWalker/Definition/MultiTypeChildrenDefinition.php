<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\TreeWalker\Definition;

use RZ\Roadiz\CoreBundle\Api\TreeWalker\NodeSourceWalkerContext;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\TreeWalker\Definition\ContextualDefinitionTrait;
use RZ\TreeWalker\WalkerContextInterface;

final class MultiTypeChildrenDefinition
{
    use ContextualDefinitionTrait;

    /**
     * @param array<string> $types
     */
    public function __construct(
        private readonly WalkerContextInterface $context,
        private readonly array $types,
        private readonly bool $onlyVisible = true,
    ) {
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
        $bag = $this->context->getNodeTypesBag();
        /** @var NodeType[] $nodeTypes */
        $nodeTypes = array_map(function (string $singleType) use ($bag) {
            return $bag->get($singleType);
        }, $this->types);
        $criteria = [
            'node.parent' => $source->getNode(),
            'translation' => $source->getTranslation(),
            'node.nodeType' => $nodeTypes,
        ];
        if ($this->onlyVisible) {
            $criteria['node.visible'] = true;
        }
        if (1 === count($nodeTypes)) {
            $entityName = $nodeTypes[0]->getSourceEntityFullQualifiedClassName();
        } else {
            $entityName = NodesSources::class;
        }
        // @phpstan-ignore-next-line
        $children = $this->context
            ->getManagerRegistry()
            ->getRepository($entityName)
            ->findBy($criteria, [
                'node.position' => 'ASC',
            ]);

        $this->context->getStopwatch()->stop(self::class);

        return $children;
    }
}
