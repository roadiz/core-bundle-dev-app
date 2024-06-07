<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Realm;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @method Realm|null findOneByName(string $name)
 * @method Realm|null find($id, $lockMode = null, $lockVersion = null)
 * @method Realm|null findOneBy(array $criteria, array $orderBy = null)
 * @method Realm[]    findAll()
 * @method Realm[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends EntityRepository<Realm>
 */
final class RealmRepository extends EntityRepository
{
    public function __construct(ManagerRegistry $registry, EventDispatcherInterface $dispatcher)
    {
        parent::__construct($registry, Realm::class, $dispatcher);
    }

    public function findByNode(Node $node): array
    {
        $qb = $this->createQueryBuilder('r');
        $qb->innerJoin('r.realmNodes', 'rn')
            ->andWhere($qb->expr()->in('rn.node', ':node'))
            ->andWhere($qb->expr()->isNotNull('rn.realm'))
            ->setParameter('node', $node);

        return $qb->getQuery()->setCacheable(true)->getResult();
    }

    public function findByNodeWithSerializationGroup(Node $node): array
    {
        $qb = $this->createQueryBuilder('r');
        $qb->innerJoin('r.realmNodes', 'rn')
            ->andWhere($qb->expr()->in('rn.node', ':node'))
            ->andWhere($qb->expr()->isNotNull('rn.realm'))
            ->andWhere($qb->expr()->isNotNull('r.serializationGroup'))
            ->setParameter('node', $node);

        return $qb->getQuery()->setCacheable(true)->getResult();
    }

    public function findByNodeAndBehaviour(Node $node, string $realmBehaviour): array
    {
        $qb = $this->createQueryBuilder('r');
        $qb->innerJoin('r.realmNodes', 'rn')
            ->andWhere($qb->expr()->in('rn.node', ':node'))
            ->andWhere($qb->expr()->eq('r.behaviour', ':behaviour'))
            ->andWhere($qb->expr()->isNotNull('rn.realm'))
            ->setParameter('node', $node)
            ->setParameter('behaviour', $realmBehaviour);

        return $qb->getQuery()->setCacheable(true)->getResult();
    }

    public function countWithSerializationGroup(): int
    {
        $qb = $this->createQueryBuilder('r');
        $qb->select($qb->expr()->count('r'))
            ->andWhere($qb->expr()->isNotNull('r.serializationGroup'));

        return intval($qb->getQuery()->getSingleScalarResult());
    }
}
