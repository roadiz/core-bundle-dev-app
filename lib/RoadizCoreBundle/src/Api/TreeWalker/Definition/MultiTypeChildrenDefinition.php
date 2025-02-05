<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\TreeWalker\Definition;

use RZ\Roadiz\CoreBundle\Api\TreeWalker\NodeSourceWalkerContext;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository;
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

        if (1 === count($nodeTypes)) {
            $nodeType = array_shift($nodeTypes);
            $entityName = $nodeType->getSourceEntityFullQualifiedClassName();
        } else {
            $entityName = NodesSources::class;
        }

        /** @var NodesSourcesRepository $repository */
        $repository = $this->context->getManagerRegistry()->getRepository($entityName);
        $alias = 'o';
        $qb = $repository->alterQueryBuilderWithAuthorizationChecker(
            $repository->createQueryBuilder($alias),
            $alias
        );

        $qb->select([$alias, 'node'])
            ->innerJoin($alias.'.node', 'node')
            ->andWhere('node.parent = :parent')
            ->andWhere($alias.'.translation = :translation')
            ->addOrderBy('node.position', 'ASC')
            ->setParameter('parent', $source->getNode())
            ->setParameter('translation', $source->getTranslation());

        if ($this->onlyVisible) {
            $qb->andWhere('node.visible = :visible')
                ->setParameter('visible', true);
        }
        if (NodesSources::class === $entityName) {
            $qb->andWhere($qb->expr()->orX(
                ...array_map(
                    fn (NodeType $nodeType) => $qb->expr()->isInstanceOf($alias, $nodeType->getSourceEntityFullQualifiedClassName()),
                    $nodeTypes
                )
            ));
        }

        $this->context->getStopwatch()->stop(self::class);

        return $qb->getQuery()->getResult();
    }
}
