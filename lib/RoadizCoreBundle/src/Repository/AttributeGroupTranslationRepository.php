<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\AttributeGroupTranslation;
use RZ\Roadiz\CoreBundle\Model\AttributeGroupTranslationInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends EntityRepository<AttributeGroupTranslation>
 */
final class AttributeGroupTranslationRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($registry, AttributeGroupTranslation::class, $dispatcher);
    }

    public function findOneByNameAndLocale(string $name, string $locale): ?AttributeGroupTranslationInterface
    {
        $qb = $this->createQueryBuilder('agt');
        return $qb->innerJoin('agt.translation', 't')
            ->andWhere($qb->expr()->eq('t.locale', ':locale'))
            ->andWhere($qb->expr()->eq('agt.name', ':name'))
            ->setParameter('locale', $locale)
            ->setParameter('name', $name)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
