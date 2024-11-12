<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Repository\EntityRepository;
use RZ\Roadiz\TwoFactorBundle\Entity\TwoFactorUser;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends EntityRepository<TwoFactorUser>
 */
class TwoFactorUserRepository extends EntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct($registry, TwoFactorUser::class, $dispatcher);
    }
}
