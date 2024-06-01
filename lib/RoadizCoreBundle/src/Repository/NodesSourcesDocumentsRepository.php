<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends EntityRepository<NodesSourcesDocuments>
 */
final class NodesSourcesDocumentsRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($registry, NodesSourcesDocuments::class, $dispatcher);
    }

    /**
     * @param NodesSources $nodeSource
     * @param NodeTypeFieldInterface $field
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @deprecated Use getLatestPositionForFieldName instead
     */
    public function getLatestPosition(NodesSources $nodeSource, NodeTypeFieldInterface $field): int
    {
        $query = $this->_em->createQuery('
            SELECT MAX(nsd.position) FROM RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments nsd
            WHERE nsd.nodeSource = :nodeSource AND nsd.fieldName = :fieldName')
                    ->setParameter('nodeSource', $nodeSource)
                    ->setParameter('fieldName', $field->getName());

        $latestPosition = $query->getSingleScalarResult();

        return is_numeric($latestPosition) ? (int) $latestPosition : 0;
    }

    public function getLatestPositionForFieldName(NodesSources $nodeSource, string $fieldName): int
    {
        $query = $this->_em->createQuery('
            SELECT MAX(nsd.position) FROM RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments nsd
            WHERE nsd.nodeSource = :nodeSource AND nsd.fieldName = :fieldName')
                    ->setParameter('nodeSource', $nodeSource)
                    ->setParameter('fieldName', $fieldName);

        $latestPosition = $query->getSingleScalarResult();

        return is_numeric($latestPosition) ? (int) $latestPosition : 0;
    }
}
