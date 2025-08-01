<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\RealmNode;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @method RealmNode|null findOneByName(string $name)
 * @method RealmNode|null find($id, $lockMode = null, $lockVersion = null)
 * @method RealmNode|null findOneBy(array $criteria, array $orderBy = null)
 * @method RealmNode[]    findAll()
 * @method RealmNode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends EntityRepository<RealmNode>
 */
final class RealmNodeRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct($registry, RealmNode::class, $dispatcher);
    }

    public function findByNodeIdsAndRealmId(array $nodeIds, int|string $realmId): array
    {
        $nodeIds = array_filter($nodeIds);
        if (empty($nodeIds)) {
            return [];
        }

        $qb = $this->createQueryBuilder('rn');
        $qb->andWhere($qb->expr()->in('rn.node', ':nodeIds'))
            ->andWhere($qb->expr()->eq('rn.realm', ':realmId'))
            ->setParameter('nodeIds', $nodeIds)
            ->setParameter('realmId', $realmId);

        return $qb->getQuery()->getResult();
    }
}
