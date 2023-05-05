<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\EventSubscriber;

use RZ\Roadiz\CoreBundle\Document\Message\DocumentAudioVideoMessage;
use RZ\Roadiz\CoreBundle\Document\Message\DocumentAverageColorMessage;
use RZ\Roadiz\CoreBundle\Document\Message\DocumentExifMessage;
use RZ\Roadiz\CoreBundle\Document\Message\DocumentFilesizeMessage;
use RZ\Roadiz\CoreBundle\Document\Message\DocumentPdfMessage;
use RZ\Roadiz\CoreBundle\Document\Message\DocumentRawMessage;
use RZ\Roadiz\CoreBundle\Document\Message\DocumentSizeMessage;
use RZ\Roadiz\CoreBundle\Document\Message\DocumentSvgMessage;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Documents\Events\DocumentCreatedEvent;
use RZ\Roadiz\Documents\Events\DocumentFileUpdatedEvent;
use RZ\Roadiz\Documents\Events\FilterDocumentEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class DocumentMessageDispatchSubscriber implements EventSubscriberInterface
{
    private MessageBusInterface $bus;

    /**
     * @param MessageBusInterface $bus
     */
    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // Only dispatch async message when document files are updated or created
             DocumentCreatedEvent::class => ['onFilterDocumentEvent', 0],
             DocumentFileUpdatedEvent::class => ['onFilterDocumentEvent', 0],
        ];
    }

    public function onFilterDocumentEvent(FilterDocumentEvent $event): void
    {
        $document = $event->getDocument();
        if (
            $document instanceof Document &&
            null !== $document->getId() &&
            $document->isLocal() &&
            null !== $document->getRelativePath()
        ) {
            $this->bus->dispatch(new Envelope(new DocumentRawMessage($document->getId())));
            $this->bus->dispatch(new Envelope(new DocumentFilesizeMessage($document->getId())));
            $this->bus->dispatch(new Envelope(new DocumentSizeMessage($document->getId())));
            $this->bus->dispatch(new Envelope(new DocumentAverageColorMessage($document->getId())));
            $this->bus->dispatch(new Envelope(new DocumentExifMessage($document->getId())));
            $this->bus->dispatch(new Envelope(new DocumentSvgMessage($document->getId())));
            $this->bus->dispatch(new Envelope(new DocumentAudioVideoMessage($document->getId())));
            $this->bus->dispatch(new Envelope(new DocumentPdfMessage($document->getId())));
        }
    }
}
