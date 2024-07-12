<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Redirection;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @method Redirection|null findOneByQuery(string $query)
 * @method Redirection|null find($id, $lockMode = null, $lockVersion = null)
 * @method Redirection|null findOneBy(array $criteria, array $orderBy = null)
 * @method Redirection[]    findAll()
 * @method Redirection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends EntityRepository<Redirection>
 */
final class RedirectionRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($registry, Redirection::class, $dispatcher);
    }
}
