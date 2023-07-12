<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;
use LogicException;
use RZ\Roadiz\CoreBundle\Entity\UserLogEntry;

/**
 * @method UserLogEntry|null findOneByName(string $name)
 * @method UserLogEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserLogEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserLogEntry[]    findAll()
 * @method UserLogEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends LogEntryRepository<UserLogEntry>
 */
final class UserLogEntryRepository extends LogEntryRepository implements ServiceEntityRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        $entityClass = UserLogEntry::class;
        $manager = $registry->getManagerForClass($entityClass);

        if (!$manager instanceof \Doctrine\ORM\EntityManagerInterface) {
            throw new LogicException(sprintf(
                'Could not find the entity manager for class "%s". Check your Doctrine configuration to make sure it is configured to load this entity’s metadata.',
                $entityClass
            ));
        }

        parent::__construct($manager, $manager->getClassMetadata($entityClass));
    }

    /**
     * @param \DateTime $dateTime
     * @return int The number of entries
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countAllBeforeLoggedIn(\DateTime $dateTime): int
    {
        $qb = $this->createQueryBuilder('l');
        // @phpstan-ignore-next-line
        return $qb
            ->select($qb->expr()->countDistinct('l'))
            ->where($qb->expr()->lt('l.loggedAt', ':loggedAt'))
            ->setParameter('loggedAt', $dateTime)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @param \DateTime $dateTime
     * @return int The number of deleted entries
     */
    public function deleteAllBeforeLoggedIn(\DateTime $dateTime): int
    {
        $qb = $this->createQueryBuilder('l');
        return $qb->delete(UserLogEntry::class, 'l')
            ->where($qb->expr()->lt('l.loggedAt', ':loggedAt'))
            ->setParameter('loggedAt', $dateTime)
            ->getQuery()
            ->execute()
        ;
    }

    public function deleteAllExceptCount(int $count): int
    {
        if ($count < 1) {
            throw new \InvalidArgumentException('Count must be greater than 0');
        }

        $deleteCount = 0;
        $qb = $this->createQueryBuilder('l');
        // @phpstan-ignore-next-line
        $objects = $qb->select('MAX(l.version) as maxVersion', 'l.objectId', 'l.objectClass')
            ->groupBy('l.objectId', 'l.objectClass')
            ->getQuery()
            ->getArrayResult()
        ;
        $deleteQuery = $qb->delete(UserLogEntry::class, 'l')
            ->andWhere($qb->expr()->eq('l.objectId', ':objectId'))
            ->andWhere($qb->expr()->eq('l.objectClass', ':objectClass'))
            ->andWhere($qb->expr()->lt('l.version', ':lowestVersion'))
            ->getQuery()
        ;

        foreach ($objects as $object) {
            $lowestVersion = (int) $object['maxVersion'] - $count;
            if ($lowestVersion > 1) {
                $deleteCount += $deleteQuery->execute([
                    'objectId' => $object['objectId'],
                    'objectClass' => $object['objectClass'],
                    'lowestVersion' => $lowestVersion
                ]);
            }
        }

        return $deleteCount;
    }
}
