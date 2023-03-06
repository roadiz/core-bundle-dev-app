<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\SettingGroup;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @method SettingGroup|null findOneByName(string $name)
 * @method SettingGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method SettingGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method SettingGroup[]    findAll()
 * @method SettingGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends EntityRepository<SettingGroup>
 */
final class SettingGroupRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($registry, SettingGroup::class, $dispatcher);
    }

    /**
     * @param string $name
     *
     * @return bool
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function exists(string $name): bool
    {
        $query = $this->_em->createQuery('
            SELECT COUNT(s.id) FROM RZ\Roadiz\CoreBundle\Entity\SettingGroup s
            WHERE s.name = :name')
                        ->setParameter('name', $name);

        return (bool) $query->getSingleScalarResult();
    }

    /**
     * @return array
     */
    public function findAllNames(): array
    {
        $query = $this->_em->createQuery('SELECT s.name FROM RZ\Roadiz\CoreBundle\Entity\SettingGroup s');
        return array_map('current', $query->getScalarResult());
    }
}
