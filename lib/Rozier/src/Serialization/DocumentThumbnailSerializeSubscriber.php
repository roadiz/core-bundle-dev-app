<?php

declare(strict_types=1);

namespace Themes\Rozier\Serialization;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\Metadata\StaticPropertyMetadata;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;

final class DocumentThumbnailSerializeSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly DocumentUrlGeneratorInterface $documentUrlGenerator)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [[
            'event' => 'serializer.post_serialize',
            'method' => 'onPostSerialize',
        ]];
    }

    public function onPostSerialize(ObjectEvent $event): void
    {
        $document = $event->getObject();
        $visitor = $event->getVisitor();
        $context = $event->getContext();

        if (
            $visitor instanceof SerializationVisitorInterface
            && $document instanceof Document
            && !$document->isPrivate()
            && $context->hasAttribute('groups')
            && \is_array($context->getAttribute('groups'))
            && in_array('explorer_thumbnail', $context->getAttribute('groups'))
        ) {
            $visitor->visitProperty(
                new StaticPropertyMetadata('boolean', 'processable', []),
                $document->isProcessable()
            );
            $visitor->visitProperty(
                new StaticPropertyMetadata('string', 'url', []),
                $this->documentUrlGenerator
                    ->setDocument($document)
                    ->setOptions([
                        'fit' => '250x200',
                        'quality' => 60,
                    ])
                    ->getUrl()
            );
        }
    }
}
