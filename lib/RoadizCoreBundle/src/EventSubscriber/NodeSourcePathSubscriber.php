<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesPathGeneratingEvent;
use RZ\Roadiz\CoreBundle\Routing\NodesSourcesPathAggregator;
use RZ\Roadiz\CoreBundle\Routing\NodesSourcesUrlGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class NodeSourcePathSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private NodesSourcesPathAggregator $pathAggregator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NodesSourcesPathGeneratingEvent::class => ['onNodesSourcesPath', -100],
        ];
    }

    public function onNodesSourcesPath(NodesSourcesPathGeneratingEvent $event): void
    {
        $urlGenerator = new NodesSourcesUrlGenerator(
            $this->pathAggregator,
            $event->getNodeSource(),
            $event->isForceLocale(),
            $event->isForceLocaleWithUrlAlias()
        );
        $event->setPath($urlGenerator->getNonContextualUrl($event->getTheme(), $event->getParameters()));
    }
}
