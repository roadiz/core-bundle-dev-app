<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\CustomFormFieldAttribute;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends EntityRepository<CustomFormFieldAttribute>
 */
final class CustomFormFieldAttributeRepository extends EntityRepository
{
    public function __construct(ManagerRegistry $registry, EventDispatcherInterface $dispatcher)
    {
        parent::__construct($registry, CustomFormFieldAttribute::class, $dispatcher);
    }
}
