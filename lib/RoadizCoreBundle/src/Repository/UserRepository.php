<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @method User|null findOneByName(string $name)
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends EntityRepository<User>
 */
final class UserRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($registry, User::class, $dispatcher);
    }

    /**
     * @param string $username
     *
     * @return bool
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function usernameExists($username): bool
    {
        $qb = $this->createQueryBuilder('u');
        $qb->select($qb->expr()->count('u.username'))
            ->andWhere($qb->expr()->eq('u.username', ':username'))
            ->setParameter('username', $username)
            ->setCacheable(true);

        return (bool) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param string $email
     * @return bool
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function emailExists(string $email): bool
    {
        $qb = $this->createQueryBuilder('u');
        $qb->select($qb->expr()->count('u.email'))
            ->andWhere($qb->expr()->eq('u.email', ':email'))
            ->setParameter('email', $email)
            ->setCacheable(true);

        return (bool) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Find all users that did not logged-in since a number of days, including users that never logged-in using
     * their creation date.
     *
     * @param int $days
     * @return User[]
     * @throws \Exception
     */
    public function findAllInactiveSinceDays(int $days): array
    {
        $qb = $this->createQueryBuilder('u');
        $qb->andWhere($qb->expr()->orX(
            // If user never logged in, we compare with creation date
            $qb->expr()->andX(
                $qb->expr()->isNull('u.lastLogin'),
                $qb->expr()->lt('u.createdAt', ':lastLogin')
            ),
            $qb->expr()->lt('u.lastLogin', ':lastLogin'),
        ))->setParameter('lastLogin', new \DateTimeImmutable('-' . $days . ' days'));

        return $qb->getQuery()->getResult();
    }
}
