<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Setting;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @method Setting|null findOneByName(string $name)
 * @method Setting|null find($id, $lockMode = null, $lockVersion = null)
 * @method Setting|null findOneBy(array $criteria, array $orderBy = null)
 * @method Setting[]    findAll()
 * @method Setting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends EntityRepository<Setting>
 */
final class SettingRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct($registry, Setting::class, $dispatcher);
    }

    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getValue(string $name): mixed
    {
        $builder = $this->createQueryBuilder('s');
        $builder->select('s.value')
                ->andWhere($builder->expr()->eq('s.name', ':name'))
                ->setParameter(':name', $name);

        $query = $builder->getQuery();
        $query->enableResultCache(3600, 'RZSettingValue_'.$name);

        return $query->getSingleScalarResult();
    }

    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function exists(string $name): bool
    {
        $builder = $this->createQueryBuilder('s');
        $builder->select($builder->expr()->count('s.value'))
            ->andWhere($builder->expr()->eq('s.name', ':name'))
            ->setParameter(':name', $name);

        $query = $builder->getQuery();
        $query->enableResultCache(3600, 'RZSettingExists_'.$name);

        return (bool) $query->getSingleScalarResult();
    }

    /**
     * Get every Setting names.
     */
    public function findAllNames(): array
    {
        $builder = $this->createQueryBuilder('s');
        $builder->select('s.name');
        $query = $builder->getQuery();
        $query->enableResultCache(3600, 'RZSettingAll');

        return array_map('current', $query->getScalarResult());
    }
}
