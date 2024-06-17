<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use RZ\Roadiz\CoreBundle\Event\FilterNodeEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeCreatedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeDuplicatedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeUpdatedEvent;
use RZ\Roadiz\CoreBundle\Node\CachedNodeOffspringResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CachedNodeOffspringEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly CachedNodeOffspringResolverInterface $cachedNodeOffspringResolver)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NodeCreatedEvent::class => 'invalidateNodeOffspringCache',
            NodeDuplicatedEvent::class => 'invalidateNodeOffspringCache',
            NodeUpdatedEvent::class => 'invalidateNodeOffspringCache',
        ];
    }

    public function invalidateNodeOffspringCache(FilterNodeEvent $event): void
    {
        $this->cachedNodeOffspringResolver->purgeOffspringCache($event->getNode());
    }
}
