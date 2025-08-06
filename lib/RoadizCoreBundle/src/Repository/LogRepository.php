<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Logger\Entity\Log;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends EntityRepository<Log>
 */
final class LogRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct($registry, Log::class, $dispatcher);
    }

    /**
     * Find the latest Log with NodesSources.
     */
    public function findLatestByNodesSources(int $maxResult = 5): Paginator
    {
        $subQb = $this->createQueryBuilder('slog');
        $subQb->select($subQb->expr()->max('slog.id'))
            ->andWhere($subQb->expr()->in('slog.entityClass', ':entityClass'))
            ->addGroupBy('slog.entityId');

        $qb = $this->createQueryBuilder('log');
        $qb->select('log.id as id')
            ->andWhere($qb->expr()->in('log.id', $subQb->getQuery()->getDQL()))
            ->orderBy('log.datetime', 'DESC')
            ->setParameter(':entityClass', [NodesSources::class, Node::class])
            ->setMaxResults($maxResult)
        ;
        $ids = $qb->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getScalarResult();

        $qb2 = $this->createQueryBuilder('log');
        $qb2->andWhere($qb2->expr()->in('log.id', ':id'))
            ->orderBy('log.datetime', 'DESC')
            ->setParameter(':id', array_map(fn (array $item) => $item['id'], $ids));

        return new Paginator($qb2->getQuery(), true);
    }

    public function findByNode(Node $node): array
    {
        $qb = $this->getAllRelatedToNodeQueryBuilder($node);

        return $qb->getQuery()->getResult();
    }

    public function getAllRelatedToNodeQueryBuilder(Node $node): QueryBuilder
    {
        $qb = $this->createQueryBuilder('obj');
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->andX(
                $qb->expr()->eq('obj.entityClass', ':nodeClass'),
                $qb->expr()->in('obj.entityId', ':nodeId')
            ),
            $qb->expr()->andX(
                $qb->expr()->eq('obj.entityClass', ':nodeSourceClass'),
                $qb->expr()->in('obj.entityId', ':nodeSourceId')
            ),
        ));
        $qb->addOrderBy('obj.datetime', 'DESC');
        $qb->setParameter('nodeClass', Node::class);
        $qb->setParameter('nodeSourceClass', NodesSources::class);
        $qb->setParameter('nodeId', [$node->getId()]);
        $qb->setParameter('nodeSourceId', $node->getNodeSources()->map(fn (NodesSources $ns) => $ns->getId())->toArray());

        return $qb;
    }
}
