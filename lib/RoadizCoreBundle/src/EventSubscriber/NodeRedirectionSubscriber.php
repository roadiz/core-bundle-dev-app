<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Event\Cache\CachePurgeRequestEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodePathChangedEvent;
use RZ\Roadiz\CoreBundle\Node\NodeMover;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribe to Node, NodesSources and UrlAlias event to clear ns url cache.
 */
final readonly class NodeRedirectionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private NodeMover $nodeMover,
        private string $kernelEnvironment,
        private PreviewResolverInterface $previewResolver,
        private NodeTypes $nodeTypesBag,
    ) {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            NodePathChangedEvent::class => 'redirectOldPaths',
        ];
    }

    /**
     * Empty nodeSources Url cache.
     */
    public function redirectOldPaths(
        NodePathChangedEvent $event,
        string $eventName,
        EventDispatcherInterface $dispatcher,
    ): void {
        $node = $event->getNode();
        if (
            'prod' === $this->kernelEnvironment
            && !$this->previewResolver->isPreview()
            && $node->isPublished()
            && $this->nodeTypesBag->get($node->getNodeTypeName())?->isReachable()
            && count($event->getPaths()) > 0
        ) {
            $this->nodeMover->redirectAll($node, $event->getPaths());
            $dispatcher->dispatch(new CachePurgeRequestEvent());
        }
    }
}
