<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesToNodes;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @package RZ\Roadiz\CoreBundle\Repository
 * @extends EntityRepository<NodesToNodes>
 */
final class NodesToNodesRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($registry, NodesToNodes::class, $dispatcher);
    }

    /**
     * @param Node $node
     * @param NodeTypeField $field
     *
     * @return integer
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getLatestPosition(Node $node, NodeTypeField $field): int
    {
        $query = $this->_em->createQuery('
            SELECT MAX(ntn.position) FROM RZ\Roadiz\CoreBundle\Entity\NodesToNodes ntn
            WHERE ntn.nodeA = :nodeA AND ntn.field = :field')
                    ->setParameter('nodeA', $node)
                    ->setParameter('field', $field);

        return (int) $query->getSingleScalarResult();
    }
}
