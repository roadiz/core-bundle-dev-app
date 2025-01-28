<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @deprecated nodeTypes will be static in future Roadiz versions
 *
 * @extends EntityRepository<NodeType>
 */
final class NodeTypeRepository extends EntityRepository implements NodeTypeRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct($registry, NodeType::class, $dispatcher);
    }

    public function findAll(): array
    {
        $qb = $this->createQueryBuilder('nt');
        $qb->addSelect('ntf')
            ->leftJoin('nt.fields', 'ntf')
            ->addOrderBy('nt.name', 'ASC')
            ->setCacheable(true);

        return $qb->getQuery()->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneByName(string $name): ?NodeType
    {
        return $this->createQueryBuilder('nt')
            ->where('nt.name = :name')
            ->setParameter('name', $name)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
