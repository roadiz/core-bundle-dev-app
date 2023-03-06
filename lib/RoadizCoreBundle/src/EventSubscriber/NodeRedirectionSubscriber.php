<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use RZ\Roadiz\CoreBundle\Event\Cache\CachePurgeRequestEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodePathChangedEvent;
use RZ\Roadiz\CoreBundle\Node\NodeMover;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Subscribe to Node, NodesSources and UrlAlias event to clear ns url cache.
 */
class NodeRedirectionSubscriber implements EventSubscriberInterface
{
    protected NodeMover $nodeMover;
    protected KernelInterface $kernel;
    protected PreviewResolverInterface $previewResolver;

    /**
     * @param NodeMover $nodeMover
     * @param KernelInterface $kernel
     * @param PreviewResolverInterface $previewResolver
     */
    public function __construct(
        NodeMover $nodeMover,
        KernelInterface $kernel,
        PreviewResolverInterface $previewResolver
    ) {
        $this->nodeMover = $nodeMover;
        $this->kernel = $kernel;
        $this->previewResolver = $previewResolver;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NodePathChangedEvent::class => 'redirectOldPaths',
            '\RZ\Roadiz\Core\Events\Node\NodePathChangedEvent' => 'redirectOldPaths'
        ];
    }

    /**
     * Empty nodeSources Url cache
     *
     * @param NodePathChangedEvent     $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $dispatcher
     */
    public function redirectOldPaths(
        NodePathChangedEvent $event,
        string $eventName,
        EventDispatcherInterface $dispatcher
    ): void {
        if (
            $this->kernel->getEnvironment() === 'prod' &&
            !$this->previewResolver->isPreview() &&
            null !== $event->getNode() &&
            $event->getNode()->isPublished() &&
            $event->getNode()->getNodeType()->isReachable() &&
            count($event->getPaths()) > 0
        ) {
            $this->nodeMover->redirectAll($event->getNode(), $event->getPaths());
            $dispatcher->dispatch(new CachePurgeRequestEvent());
        }
    }
}
