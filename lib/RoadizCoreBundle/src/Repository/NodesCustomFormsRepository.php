<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesCustomForms;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @package RZ\Roadiz\CoreBundle\Repository
 * @extends EntityRepository<\RZ\Roadiz\CoreBundle\Entity\NodesCustomForms>
 */
final class NodesCustomFormsRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($registry, NodesCustomForms::class, $dispatcher);
    }

    /**
     * @param Node $node
     * @param NodeTypeFieldInterface $field
     * @return int
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @deprecated Use getLatestPositionForFieldName instead
     */
    public function getLatestPosition(Node $node, NodeTypeFieldInterface $field): int
    {
        $query = $this->_em->createQuery('
            SELECT MAX(ncf.position) FROM RZ\Roadiz\CoreBundle\Entity\NodesCustomForms ncf
            WHERE ncf.node = :node AND ncf.fieldName = :fieldName')
                    ->setParameter('node', $node)
                    ->setParameter('fieldName', $field->getName());

        $latestPosition = $query->getSingleScalarResult();

        return is_numeric($latestPosition) ? (int) $latestPosition : 0;
    }

    public function getLatestPositionForFieldName(Node $node, string $fieldName): int
    {
        $query = $this->_em->createQuery('
            SELECT MAX(ncf.position) FROM RZ\Roadiz\CoreBundle\Entity\NodesCustomForms ncf
            WHERE ncf.node = :node AND ncf.fieldName = :fieldName')
                    ->setParameter('node', $node)
                    ->setParameter('fieldName', $fieldName);

        $latestPosition = $query->getSingleScalarResult();

        return is_numeric($latestPosition) ? (int) $latestPosition : 0;
    }
}
