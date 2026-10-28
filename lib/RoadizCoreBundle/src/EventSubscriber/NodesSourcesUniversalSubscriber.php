<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesUpdatedEvent;
use RZ\Roadiz\CoreBundle\Node\UniversalDataDuplicator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class NodesSourcesUniversalSubscriber implements EventSubscriberInterface
{
    public function __construct(private ManagerRegistry $managerRegistry, private UniversalDataDuplicator $universalDataDuplicator)
    {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            NodesSourcesUpdatedEvent::class => 'duplicateUniversalContents',
        ];
    }

    public function duplicateUniversalContents(NodesSourcesUpdatedEvent $event): void
    {
        $source = $event->getNodeSource();

        /*
         * Flush only if duplication happened.
         */
        if (true === $this->universalDataDuplicator->duplicateUniversalContents($source)) {
            $this->managerRegistry->getManager()->flush();
        }
    }
}
