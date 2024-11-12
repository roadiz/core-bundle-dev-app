<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class CustomFormAnswerRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct($registry, CustomFormAnswer::class, $dispatcher);
    }

    protected function getCustomFormSubmittedBeforeQueryBuilder(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('cfa');

        return $qb->andWhere($qb->expr()->eq('cfa.customForm', ':customForm'))
                  ->andWhere($qb->expr()->lte('cfa.submittedAt', ':submittedAt'));
    }

    /**
     * @return Paginator<CustomFormAnswer>
     */
    public function findByCustomFormSubmittedBefore(CustomForm $customForm, \DateTime $submittedAt): Paginator
    {
        $qb = $this->getCustomFormSubmittedBeforeQueryBuilder()
            ->setParameter(':customForm', $customForm)
            ->setParameter(':submittedAt', $submittedAt);

        return new Paginator($qb->getQuery());
    }

    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function deleteByCustomFormSubmittedBefore(CustomForm $customForm, \DateTime $submittedAt): int
    {
        $qb = $this->getCustomFormSubmittedBeforeQueryBuilder()
            ->delete()
            ->setParameter(':customForm', $customForm)
            ->setParameter(':submittedAt', $submittedAt);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
