<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\TreeWalker\Definition;

use Doctrine\ORM\QueryBuilder;
use RZ\Roadiz\CoreBundle\Api\TreeWalker\NodeSourceWalkerContext;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository;

trait NodeSourceDefinitionTrait
{
    /**
     * @return array<NodeType> $nodeTypes
     */
    abstract protected function getNodeTypes(NodeTypes $nodeTypesBag): array;

    protected function getQueryBuilder(
        NodesSources $parent,
    ): QueryBuilder {
        if (!($this->context instanceof NodeSourceWalkerContext)) {
            throw new \InvalidArgumentException('Context should be instance of '.NodeSourceWalkerContext::class);
        }

        $nodeTypes = $this->getNodeTypes($this->context->getNodeTypesBag());
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
            ->setParameter('parent', $parent->getNode())
            ->setParameter('translation', $parent->getTranslation());

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

        return $qb;
    }
}
