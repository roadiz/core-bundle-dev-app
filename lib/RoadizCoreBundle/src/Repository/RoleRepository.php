<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Role;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Do not extend Roadiz or Service repository to prevent cyclic dependencies.
 *
 * @method Role|null find($id, $lockMode = null, $lockVersion = null)
 * @method Role|null findOneBy(array $criteria, array $orderBy = null)
 * @method Role[]    findAll()
 * @method Role[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends EntityRepository<Role>
 */
final class RoleRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct($registry, Role::class, $dispatcher);
    }

    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countByName(string $roleName): int
    {
        $roleName = Role::cleanName($roleName);

        $query = $this->createQueryBuilder('r');
        $query->select($query->expr()->countDistinct('r'))
              ->andWhere($query->expr()->eq('r.name', ':name'))
              ->setParameter('name', $roleName);

        return (int) $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws ORMException
     */
    public function findOneByName(string $roleName): Role
    {
        $roleName = Role::cleanName($roleName);

        $query = $this->createQueryBuilder('r');
        $query->andWhere($query->expr()->eq('r.name', ':name'))
              ->setMaxResults(1)
              ->setParameter('name', $roleName);

        $role = $query->getQuery()->getOneOrNullResult();
        if (null === $role) {
            $role = new Role($roleName);
            $this->_em->persist($role);
            $this->_em->flush();
        }

        return $role;
    }

    /**
     * Get every Role names except for ROLE_SUPERADMIN.
     */
    public function getAllBasicRoleName(): array
    {
        $builder = $this->createQueryBuilder('r');
        $builder->select('r.name')
              ->andWhere($builder->expr()->neq('r.name', ':name'))
              ->setParameter('name', Role::ROLE_SUPERADMIN);

        $query = $builder->getQuery();
        $query->enableResultCache(3600, 'RZRoleAllBasic');

        return array_map('current', $query->getScalarResult());
    }

    /**
     * Get every Role names.
     */
    public function getAllRoleName(): array
    {
        $builder = $this->createQueryBuilder('r');
        $builder->select('r.name');

        $query = $builder->getQuery();
        $query->enableResultCache(3600, 'RZRoleAll');

        return array_map('current', $query->getScalarResult());
    }
}
