<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesToNodes;
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
     * @param NodeTypeFieldInterface $field
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @deprecated Use getLatestPositionForFieldName instead
     */
    public function getLatestPosition(Node $node, NodeTypeFieldInterface $field): int
    {
        $query = $this->_em->createQuery('
            SELECT MAX(ntn.position) FROM RZ\Roadiz\CoreBundle\Entity\NodesToNodes ntn
            WHERE ntn.nodeA = :nodeA AND ntn.fieldName = :fieldName')
                    ->setParameter('nodeA', $node)
                    ->setParameter('fieldName', $field->getName());

        $latestPosition = $query->getSingleScalarResult();

        return is_numeric($latestPosition) ? (int) $latestPosition : 0;
    }

    public function getLatestPositionForFieldName(Node $node, string $fieldName): int
    {
        $query = $this->_em->createQuery('
            SELECT MAX(ntn.position) FROM RZ\Roadiz\CoreBundle\Entity\NodesToNodes ntn
            WHERE ntn.nodeA = :nodeA AND ntn.fieldName = :fieldName')
                    ->setParameter('nodeA', $node)
                    ->setParameter('fieldName', $fieldName);

        $latestPosition = $query->getSingleScalarResult();

        return is_numeric($latestPosition) ? (int) $latestPosition : 0;
    }
}
