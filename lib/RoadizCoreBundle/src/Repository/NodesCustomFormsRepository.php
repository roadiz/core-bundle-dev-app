<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
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
     * @param Node          $node
     * @param NodeTypeField $field
     *
     * @return integer
     */
    public function getLatestPosition(Node $node, NodeTypeField $field)
    {
        $query = $this->_em->createQuery('
            SELECT MAX(ncf.position) FROM RZ\Roadiz\CoreBundle\Entity\NodesCustomForms ncf
            WHERE ncf.node = :node AND ncf.field = :field')
                    ->setParameter('node', $node)
                    ->setParameter('field', $field);

        return (int) $query->getSingleScalarResult();
    }
}
