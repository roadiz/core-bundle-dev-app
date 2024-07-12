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
    public function __construct(private readonly NodesSourcesUrlsCacheClearer $cacheClearer)
    {
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            NodesSourcesCreatedEvent::class => 'onPurgeRequest',
            NodesSourcesDeletedEvent::class => 'onPurgeRequest',
            TranslationUpdatedEvent::class => 'onPurgeRequest',
            TranslationDeletedEvent::class => 'onPurgeRequest',
            NodeDeletedEvent::class => 'onPurgeRequest',
            NodeUndeletedEvent::class => 'onPurgeRequest',
            NodeUpdatedEvent::class => 'onPurgeRequest',
            UrlAliasCreatedEvent::class => 'onPurgeRequest',
            UrlAliasUpdatedEvent::class => 'onPurgeRequest',
            UrlAliasDeletedEvent::class => 'onPurgeRequest',
            'workflow.node.completed' => 'onPurgeRequest',
            CachePurgeRequestEvent::class => ['onPurgeRequest', 3],
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
