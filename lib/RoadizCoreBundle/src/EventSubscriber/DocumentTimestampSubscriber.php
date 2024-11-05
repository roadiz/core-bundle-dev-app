<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use RZ\Roadiz\Core\AbstractEntities\AbstractDateTimed;
use RZ\Roadiz\CoreBundle\Event\Document\DocumentTranslationUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DocumentTimestampSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            DocumentTranslationUpdatedEvent::class => 'onDocumentTranslationUpdatedEvent',
        ];
    }

    public function onDocumentTranslationUpdatedEvent(DocumentTranslationUpdatedEvent $event): void
    {
        $document = $event->getDocument();
        if ($document instanceof AbstractDateTimed) {
            $document->setUpdatedAt(new \DateTime());
        }
    }
}
