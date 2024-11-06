<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use Psr\Cache\CacheItemPoolInterface;
use RZ\Roadiz\CoreBundle\Event\Redirection\PostCreatedRedirectionEvent;
use RZ\Roadiz\CoreBundle\Event\Redirection\PostDeletedRedirectionEvent;
use RZ\Roadiz\CoreBundle\Event\Redirection\PostUpdatedRedirectionEvent;
use RZ\Roadiz\CoreBundle\Event\Redirection\RedirectionEvent;
use RZ\Roadiz\CoreBundle\Routing\RedirectionPathResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RedirectionCacheSubscriber implements EventSubscriberInterface
{
    private CacheItemPoolInterface $cacheAdapter;

    public function __construct(CacheItemPoolInterface $cacheAdapter)
    {
        $this->cacheAdapter = $cacheAdapter;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostCreatedRedirectionEvent::class => 'clearCache',
            PostDeletedRedirectionEvent::class => 'clearCache',
            PostUpdatedRedirectionEvent::class => 'clearCache',
        ];
    }

    public function clearCache(RedirectionEvent $event): void
    {
        $this->cacheAdapter->deleteItem(RedirectionPathResolver::CACHE_KEY);
    }
}
