<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use RZ\Roadiz\Core\AbstractEntities\AbstractDateTimed;
use RZ\Roadiz\CoreBundle\Event\Tag\TagUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TagTimestampSubscriber implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            TagUpdatedEvent::class => 'onTagUpdatedEvent'
        ];
    }

    public function onTagUpdatedEvent(TagUpdatedEvent $event): void
    {
        $tag = $event->getTag();
        if ($tag instanceof AbstractDateTimed) {
            $tag->setUpdatedAt(new \DateTime());
        }
    }
}
