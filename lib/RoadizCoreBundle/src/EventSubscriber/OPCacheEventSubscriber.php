<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use RZ\Roadiz\CoreBundle\Cache\Clearer\OPCacheClearer;
use RZ\Roadiz\CoreBundle\Event\Cache\CachePurgeRequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class OPCacheEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CachePurgeRequestEvent::class => ['onPurgeRequest', 3],
        ];
    }

    public function onPurgeRequest(CachePurgeRequestEvent $event): void
    {
        try {
            $clearer = new OPCacheClearer();
            if (false !== $clearer->clear()) {
                $event->addMessage($clearer->getOutput(), self::class, 'OPCode cache');
            }
        } catch (\Exception $e) {
            $event->addError($e->getMessage(), self::class, 'OPCode cache');
        }
    }
}
