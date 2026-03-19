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
use Symfony\Component\Cache\ResettableInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Subscribe to Translation event to clear result cache.
 */
final readonly class TranslationSubscriber implements EventSubscriberInterface
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }

    #[\Override]
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

        if ($manager instanceof EntityManagerInterface) {
            $configuration = $manager->getConfiguration();

            // Doctrine ORM result cache pool (PSR-6, Doctrine ORM 2.7+)
            $resultCache = $configuration->getResultCache();
            $resultCache?->clear();
            if ($resultCache instanceof ResettableInterface) {
                $resultCache->reset();
            }

            // Legacy Doctrine result cache provider
            if ($configuration->getResultCacheImpl() instanceof CacheProvider) {
                $configuration->getResultCacheImpl()->deleteAll();
            }
        }

        $dispatcher->dispatch(new CachePurgeRequestEvent());
    }
}
