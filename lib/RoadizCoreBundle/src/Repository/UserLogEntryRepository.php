<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\UserLogEntry;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @method UserLogEntry|null findOneByName(string $name)
 * @method UserLogEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserLogEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserLogEntry[]    findAll()
 * @method UserLogEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends EntityRepository<UserLogEntry>
 */
final class UserLogEntryRepository extends EntityRepository
{
    public function __construct(ManagerRegistry $registry, EventDispatcherInterface $dispatcher)
    {
        parent::__construct($registry, UserLogEntry::class, $dispatcher);
    }
}
