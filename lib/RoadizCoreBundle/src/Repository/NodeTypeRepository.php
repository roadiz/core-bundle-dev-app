<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @package RZ\Roadiz\CoreBundle\Repository
 * @extends EntityRepository<NodeType>
 */
final class NodeTypeRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($registry, NodeType::class, $dispatcher);
    }
    /**
     * @return array
     */
    public function findAll(): array
    {
        $qb = $this->createQueryBuilder('nt');
        $qb->addSelect('ntf')
            ->leftJoin('nt.fields', 'ntf')
            ->addOrderBy('nt.name', 'ASC')
            ->setCacheable(true);

        return $qb->getQuery()->getResult();
    }
}
