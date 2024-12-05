<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Bag\LazyParameterBag;
use RZ\Roadiz\CoreBundle\Entity\Role;
use RZ\Roadiz\CoreBundle\Event\Role\PreCreatedRoleEvent;
use RZ\Roadiz\CoreBundle\Event\Role\PreDeletedRoleEvent;
use RZ\Roadiz\CoreBundle\Event\Role\PreUpdatedRoleEvent;
use RZ\Roadiz\CoreBundle\Event\Role\RoleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class RoleSubscriber implements EventSubscriberInterface
{
    public function __construct(private ManagerRegistry $managerRegistry, private ?LazyParameterBag $roles)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PreCreatedRoleEvent::class => 'onRoleChanged',
            PreUpdatedRoleEvent::class => 'onRoleChanged',
            PreDeletedRoleEvent::class => 'onRoleChanged',
        ];
    }

    public function onRoleChanged(RoleEvent $event): void
    {
        $manager = $this->managerRegistry->getManagerForClass(Role::class);
        // Clear result cache
        if (
            $manager instanceof EntityManagerInterface
            && $manager->getConfiguration()->getResultCacheImpl() instanceof CacheProvider
        ) {
            $manager->getConfiguration()->getResultCacheImpl()->deleteAll();
        }
        // Clear memory roles bag
        $this->roles?->reset();
    }
}
