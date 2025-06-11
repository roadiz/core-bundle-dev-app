<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use RZ\Roadiz\CoreBundle\Event\FilterNodeEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeCreatedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeUpdatedEvent;
use RZ\Roadiz\CoreBundle\Event\Realm\AbstractRealmNodeEvent;
use RZ\Roadiz\CoreBundle\Event\Realm\NodeJoinedRealmEvent;
use RZ\Roadiz\CoreBundle\Event\Realm\NodeLeftRealmEvent;
use RZ\Roadiz\CoreBundle\Message\ApplyRealmNodeInheritanceMessage;
use RZ\Roadiz\CoreBundle\Message\CleanRealmNodeInheritanceMessage;
use RZ\Roadiz\CoreBundle\Message\SearchRealmNodeInheritanceMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class RealmNodeInheritanceSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $bus)
    {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            NodeJoinedRealmEvent::class => 'onNodeJoinedRealm',
            NodeLeftRealmEvent::class => 'onNodeLeftRealm',
            NodeUpdatedEvent::class => 'onNodeUpdated',
            NodeCreatedEvent::class => 'onNodeUpdated',
        ];
    }

    public function onNodeUpdated(FilterNodeEvent $event): void
    {
        /*
         * Do not store objects in async operations to avoid issues with Doctrine Object manager
         */
        $this->bus->dispatch(new Envelope(new SearchRealmNodeInheritanceMessage(
            $event->getNode()->getId()
        )));
    }

    public function onNodeJoinedRealm(AbstractRealmNodeEvent $event): void
    {
        /*
         * Do not store objects in async operations to avoid issues with Doctrine Object manager
         */
        $this->bus->dispatch(new Envelope(new ApplyRealmNodeInheritanceMessage(
            $event->getRealmNode()->getNode()->getId(),
            $event->getRealmNode()->getRealm()->getId()
        )));
    }

    public function onNodeLeftRealm(AbstractRealmNodeEvent $event): void
    {
        /*
         * Do not store objects in async operations to avoid issues with Doctrine Object manager
         */
        $this->bus->dispatch(new Envelope(new CleanRealmNodeInheritanceMessage(
            $event->getRealmNode()->getNode()->getId(),
            $event->getRealmNode()->getRealm()->getId()
        )));
    }
}
