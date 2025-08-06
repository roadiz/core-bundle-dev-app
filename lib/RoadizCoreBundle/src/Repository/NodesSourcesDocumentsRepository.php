<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends EntityRepository<NodesSourcesDocuments>
 */
final class NodesSourcesDocumentsRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct($registry, NodesSourcesDocuments::class, $dispatcher);
    }

    /**
     * @return NodesSourcesDocuments[]
     */
    public function findByNodesSourcesAndFieldName(NodesSources $nodeSource, string $fieldName): array
    {
        $queryBuilder = $this->createQueryBuilder('nsd');
        $queryBuilder->andWhere($queryBuilder->expr()->eq('nsd.nodeSource', ':nodeSource'))
            ->andWhere($queryBuilder->expr()->eq('nsd.fieldName', ':fieldName'))
            ->orderBy('nsd.position', 'ASC')
            ->setParameter('nodeSource', $nodeSource)
            ->setParameter('fieldName', $fieldName);

        return $queryBuilder->getQuery()->getResult();
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
