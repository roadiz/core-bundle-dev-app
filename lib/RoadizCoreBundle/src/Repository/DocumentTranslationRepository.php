<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends EntityRepository<DocumentTranslation>
 */
final class DocumentTranslationRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct($registry, DocumentTranslation::class, $dispatcher);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneWithDocument(int $id): ?DocumentTranslation
    {
        $qb = $this->createQueryBuilder('dt');
        $qb->select('dt, d')
            ->innerJoin('dt.document', 'd')
            ->andWhere($qb->expr()->eq('dt.id', ':id'))
            ->setMaxResults(1)
            ->setParameter(':id', $id);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
