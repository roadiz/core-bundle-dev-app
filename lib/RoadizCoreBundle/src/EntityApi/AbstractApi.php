<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityApi;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;

/**
 * @deprecated Use EntityRepository directly
 */
abstract class AbstractApi
{
    public function __construct(protected ManagerRegistry $managerRegistry)
    {
    }

    abstract public function getRepository(): EntityRepository;

    /**
     * Return an array of entities matching criteria array.
     */
    abstract public function getBy(array $criteria): array|Paginator;

    /**
     * Return one entity matching criteria array.
     */
    abstract public function getOneBy(array $criteria): ?PersistableInterface;

    /**
     * Count entities matching criteria array.
     */
    abstract public function countBy(array $criteria): int;
}
