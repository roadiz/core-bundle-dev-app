<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\AttributeDocuments;
use RZ\Roadiz\CoreBundle\Model\AttributeInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends EntityRepository<AttributeDocuments>
 */
final class AttributeDocumentsRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct($registry, AttributeDocuments::class, $dispatcher);
    }

    /**
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLatestPosition(AttributeInterface $attribute): int
    {
        $qb = $this->createQueryBuilder('ad');
        $qb->select($qb->expr()->max('ad.position'))
            ->andWhere($qb->expr()->eq('ad.attribute', ':attribute'))
            ->setParameter('attribute', $attribute);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
