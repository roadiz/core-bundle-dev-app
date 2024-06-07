<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityApi;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @deprecated Use EntityRepository directly
 */
abstract class AbstractApi
{
    protected ManagerRegistry $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * Return entity path for current API.
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    abstract public function getRepository();

    /**
     * Return an array of entities matching criteria array.
     *
     * @param array $criteria
     * @return array|Paginator
     */
    abstract public function getBy(array $criteria);

    /**
     * Return one entity matching criteria array.
     *
     * @param array $criteria
     * @return mixed
     */
    abstract public function getOneBy(array $criteria);

    /**
     * Count entities matching criteria array.
     *
     * @param array $criteria
     * @return int
     */
    abstract public function countBy(array $criteria);
}
