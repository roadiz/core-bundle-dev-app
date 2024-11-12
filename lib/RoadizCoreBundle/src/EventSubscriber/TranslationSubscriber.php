<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Event\Cache\CachePurgeRequestEvent;
use RZ\Roadiz\CoreBundle\Event\Translation\TranslationCreatedEvent;
use RZ\Roadiz\CoreBundle\Event\Translation\TranslationDeletedEvent;
use RZ\Roadiz\CoreBundle\Event\Translation\TranslationUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Subscribe to Translation event to clear result cache.
 */
final class TranslationSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TranslationCreatedEvent::class => 'purgeCache',
            TranslationUpdatedEvent::class => 'purgeCache',
            TranslationDeletedEvent::class => 'purgeCache',
        ];
    }

    /**
     * Empty nodeSources Url cache.
     */
    public function purgeCache(Event $event, string $eventName, EventDispatcherInterface $dispatcher): void
    {
        $manager = $this->managerRegistry->getManager();
        // Clear result cache
        if (
            $manager instanceof EntityManagerInterface
            && $manager->getConfiguration()->getResultCacheImpl() instanceof CacheProvider
        ) {
            $manager->getConfiguration()->getResultCacheImpl()->deleteAll();
        }
        $dispatcher->dispatch(new CachePurgeRequestEvent());
    }
}
