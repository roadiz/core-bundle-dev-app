<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesCustomForms;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends EntityRepository<\RZ\Roadiz\CoreBundle\Entity\NodesCustomForms>
 */
final class NodesCustomFormsRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct($registry, NodesCustomForms::class, $dispatcher);
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
