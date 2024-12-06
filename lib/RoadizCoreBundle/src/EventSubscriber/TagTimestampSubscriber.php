<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use RZ\Roadiz\CoreBundle\Event\Tag\TagUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class TagTimestampSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            TagUpdatedEvent::class => 'onTagUpdatedEvent',
        ];
    }

    public function onTagUpdatedEvent(TagUpdatedEvent $event): void
    {
        $event->getTag()->setUpdatedAt(new \DateTime());
    }
}
