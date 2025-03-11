<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\StackType;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @method StackType|null findOneByName(string $name)
 * @method StackType|null find($id, $lockMode = null, $lockVersion = null)
 * @method StackType|null findOneBy(array $criteria, array $orderBy = null)
 * @method StackType[]    findAll()
 * @method StackType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends EntityRepository<StackType>
 */
final class StackTypeRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct($registry, StackType::class, $dispatcher);
    }
}
