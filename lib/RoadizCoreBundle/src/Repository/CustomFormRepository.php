<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Entity\Node;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends EntityRepository<CustomForm>
 */
final class CustomFormRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($registry, CustomForm::class, $dispatcher);
    }

    /**
     * @return CustomForm[]
     */
    public function findAllWithRetentionTime(): array
    {
        $qb = $this->createQueryBuilder('cf');
        return $qb->andWhere($qb->expr()->isNotNull('cf.retentionTime'))
            ->getQuery()
            ->getResult();
    }

    public function findByNodeAndField(Node $node, NodeTypeFieldInterface $field): array
    {
        $query = $this->_em->createQuery('
            SELECT cf FROM RZ\Roadiz\CoreBundle\Entity\CustomForm cf
            INNER JOIN cf.nodes ncf
            WHERE ncf.field = :field AND ncf.node = :node
            ORDER BY ncf.position ASC')
                        ->setParameter('field', $field)
                        ->setParameter('node', $node);

        return $query->getResult();
    }
}
