<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeDecorator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends EntityRepository<NodeTypeDecorator>
 */
final class NodeTypeDecoratorRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct($registry, NodeTypeDecorator::class, $dispatcher);
    }

    /**
     * @return NodeTypeDecorator[]
     */
    public function findByNodeType(NodeType $nodeType): array
    {
        $qb = $this->createQueryBuilder(self::NODETYPE_DECORATOR_ALIAS);
        $qb->where($qb->expr()->like(self::NODETYPE_DECORATOR_ALIAS.'.path', ':nodeTypeName'));
        $qb->setParameter('nodeTypeName', $nodeType->getName().'.%');

        return $qb->getQuery()->getResult();
    }
}
