<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends EntityRepository<NodeTypeField>
 */
final class NodeTypeFieldRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct($registry, NodeTypeField::class, $dispatcher);
    }

    public function findAvailableGroupsForNodeType(?NodeTypeInterface $nodeType): array
    {
        if (null === $nodeType) {
            return [];
        }
        $query = $this->_em->createQuery('
            SELECT partial ntf.{id,groupName} FROM RZ\Roadiz\CoreBundle\Entity\NodeTypeField ntf
            WHERE ntf.visible = true
            AND ntf.nodeType = :nodeType
            GROUP BY ntf.groupName
            ORDER BY ntf.groupName ASC
        ')->setParameter(':nodeType', $nodeType);

        return $query->getScalarResult();
    }

    public function findAllNotUniversal(?NodeTypeInterface $nodeType): array
    {
        if (null === $nodeType) {
            return [];
        }
        $qb = $this->createQueryBuilder('ntf');
        $qb->andWhere($qb->expr()->eq('ntf.nodeType', ':nodeType'))
            ->andWhere($qb->expr()->eq('ntf.universal', ':universal'))
            ->orderBy('ntf.position', 'ASC')
            ->setParameter(':nodeType', $nodeType)
            ->setParameter(':universal', false);

        return $qb->getQuery()->getResult();
    }

    public function findAllUniversal(?NodeTypeInterface $nodeType): array
    {
        if (null === $nodeType) {
            return [];
        }
        $qb = $this->createQueryBuilder('ntf');
        $qb->andWhere($qb->expr()->eq('ntf.nodeType', ':nodeType'))
            ->andWhere($qb->expr()->eq('ntf.universal', ':universal'))
            ->orderBy('ntf.position', 'ASC')
            ->setParameter(':nodeType', $nodeType)
            ->setParameter(':universal', true);

        return $qb->getQuery()->getResult();
    }

    /**
     * Get the latest position in nodeType.
     *
     * Parent can be null for tag root
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function findLatestPositionInNodeType(?NodeTypeInterface $nodeType): int
    {
        if (null === $nodeType) {
            return 0;
        }
        $query = $this->_em->createQuery('
            SELECT MAX(ntf.position)
            FROM RZ\Roadiz\CoreBundle\Entity\NodeTypeField ntf
            WHERE ntf.nodeType = :nodeType')
            ->setParameter('nodeType', $nodeType);

        return (int) $query->getSingleScalarResult();
    }
}
