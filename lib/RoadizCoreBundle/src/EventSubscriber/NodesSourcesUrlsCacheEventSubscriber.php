<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use RZ\Roadiz\CoreBundle\Cache\Clearer\NodesSourcesUrlsCacheClearer;
use RZ\Roadiz\CoreBundle\Event\Cache\CachePurgeRequestEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeDeletedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeUndeletedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeUpdatedEvent;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesCreatedEvent;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesDeletedEvent;
use RZ\Roadiz\CoreBundle\Event\Translation\TranslationDeletedEvent;
use RZ\Roadiz\CoreBundle\Event\Translation\TranslationUpdatedEvent;
use RZ\Roadiz\CoreBundle\Event\UrlAlias\UrlAliasCreatedEvent;
use RZ\Roadiz\CoreBundle\Event\UrlAlias\UrlAliasDeletedEvent;
use RZ\Roadiz\CoreBundle\Event\UrlAlias\UrlAliasUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class NodesSourcesUrlsCacheEventSubscriber implements EventSubscriberInterface
{
    private NodesSourcesUrlsCacheClearer $cacheClearer;

    /**
     * @param NodesSourcesUrlsCacheClearer $cacheClearer
     */
    public function __construct(NodesSourcesUrlsCacheClearer $cacheClearer)
    {
        $this->cacheClearer = $cacheClearer;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            NodesSourcesCreatedEvent::class => 'onPurgeRequest',
            '\RZ\Roadiz\Core\Events\NodesSources\NodesSourcesCreatedEvent' => 'onPurgeRequest',
            NodesSourcesDeletedEvent::class => 'onPurgeRequest',
            '\RZ\Roadiz\Core\Events\NodesSources\NodesSourcesDeletedEvent' => 'onPurgeRequest',
            TranslationUpdatedEvent::class => 'onPurgeRequest',
            '\RZ\Roadiz\Core\Events\Translation\TranslationUpdatedEvent' => 'onPurgeRequest',
            TranslationDeletedEvent::class => 'onPurgeRequest',
            '\RZ\Roadiz\Core\Events\Translation\TranslationDeletedEvent' => 'onPurgeRequest',
            NodeDeletedEvent::class => 'onPurgeRequest',
            '\RZ\Roadiz\Core\Events\Node\NodeDeletedEvent' => 'onPurgeRequest',
            NodeUndeletedEvent::class => 'onPurgeRequest',
            '\RZ\Roadiz\Core\Events\Node\NodeUndeletedEvent' => 'onPurgeRequest',
            NodeUpdatedEvent::class => 'onPurgeRequest',
            '\RZ\Roadiz\Core\Events\Node\NodeUpdatedEvent' => 'onPurgeRequest',
            UrlAliasCreatedEvent::class => 'onPurgeRequest',
            '\RZ\Roadiz\Core\Events\UrlAlias\UrlAliasCreatedEvent' => 'onPurgeRequest',
            UrlAliasUpdatedEvent::class => 'onPurgeRequest',
            '\RZ\Roadiz\Core\Events\UrlAlias\UrlAliasUpdatedEvent' => 'onPurgeRequest',
            UrlAliasDeletedEvent::class => 'onPurgeRequest',
            '\RZ\Roadiz\Core\Events\UrlAlias\UrlAliasDeletedEvent' => 'onPurgeRequest',
            'workflow.node.completed' => 'onPurgeRequest',
            CachePurgeRequestEvent::class => ['onPurgeRequest', 3],
            '\RZ\Roadiz\Core\Events\Cache\CachePurgeRequestEvent' => ['onPurgeRequest', 3],
        ];
    }

    /**
     * @param CachePurgeRequestEvent|mixed $event
     */
    public function onPurgeRequest(mixed $event): void
    {
        try {
            if (false !== $this->cacheClearer->clear()) {
                if ($event instanceof CachePurgeRequestEvent) {
                    $event->addMessage($this->cacheClearer->getOutput(), self::class, 'NodesSources URL cache');
                }
            }
        } catch (\Exception $e) {
            if ($event instanceof CachePurgeRequestEvent) {
                $event->addError($e->getMessage(), self::class, 'NodesSources URL cache');
            }
        }
    }
}
