<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesPathGeneratingEvent;
use RZ\Roadiz\CoreBundle\Routing\NodesSourcesPathAggregator;
use RZ\Roadiz\CoreBundle\Routing\NodesSourcesUrlGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NodeSourcePathSubscriber implements EventSubscriberInterface
{
    protected NodesSourcesPathAggregator $pathAggregator;

    /**
     * @param NodesSourcesPathAggregator $pathAggregator
     */
    public function __construct(NodesSourcesPathAggregator $pathAggregator)
    {
        $this->pathAggregator = $pathAggregator;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            NodesSourcesPathGeneratingEvent::class => [['onNodesSourcesPath', -100]],
            '\RZ\Roadiz\Core\Events\NodesSources\NodesSourcesPathGeneratingEvent' => [['onNodesSourcesPath', -100]],
        ];
    }

    /**
     * @param NodesSourcesPathGeneratingEvent $event
     */
    public function onNodesSourcesPath(NodesSourcesPathGeneratingEvent $event): void
    {
        $urlGenerator = new NodesSourcesUrlGenerator(
            $this->pathAggregator,
            null,
            $event->getNodeSource(),
            $event->isForceLocale(),
            $event->isForceLocaleWithUrlAlias()
        );
        $event->setPath($urlGenerator->getNonContextualUrl($event->getTheme(), $event->getParameters()));
    }
}
