<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RZ\Roadiz\CoreBundle\Event\Node\NodePathChangedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeUpdatedEvent;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesPreUpdatedEvent;
use RZ\Roadiz\CoreBundle\Node\Exception\SameNodeUrlException;
use RZ\Roadiz\CoreBundle\Node\NodeMover;
use RZ\Roadiz\CoreBundle\Node\NodeNamePolicyInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Updates node name against default node-source title is applicable.
 */
final class NodeNameSubscriber implements EventSubscriberInterface
{
    private NodeMover $nodeMover;
    private LoggerInterface $logger;
    private NodeNamePolicyInterface $nodeNamePolicy;

    /**
     * @param LoggerInterface|null $logger
     * @param NodeNamePolicyInterface $nodeNamePolicy
     * @param NodeMover $nodeMover
     */
    public function __construct(?LoggerInterface $logger, NodeNamePolicyInterface $nodeNamePolicy, NodeMover $nodeMover)
    {
        $this->logger = $logger ?? new NullLogger();
        $this->nodeNamePolicy = $nodeNamePolicy;
        $this->nodeMover = $nodeMover;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            NodesSourcesPreUpdatedEvent::class => ['onBeforeUpdate', 0],
            '\RZ\Roadiz\Core\Events\NodesSources\NodesSourcesPreUpdatedEvent' => ['onBeforeUpdate', 0],
        ];
    }

    public function onBeforeUpdate(
        NodesSourcesPreUpdatedEvent $event,
        string $eventName,
        EventDispatcherInterface $dispatcher
    ): void {
        $nodeSource = $event->getNodeSource();
        $title = $nodeSource->getTitle();

        /*
         * Update node name if dynamic option enabled and
         * default translation
         */
        if (
            "" != $title &&
            true === $nodeSource->getNode()->isDynamicNodeName() &&
            $nodeSource->getTranslation()->isDefaultTranslation()
        ) {
            $testingNodeName = $this->nodeNamePolicy->getCanonicalNodeName($nodeSource);

            /*
             * Node name won't be updated if name already taken OR
             * if it is ALREADY suffixed with a unique ID.
             */
            if (
                $testingNodeName != $nodeSource->getNode()->getNodeName() &&
                $this->nodeNamePolicy->isNodeNameValid($testingNodeName) &&
                !$this->nodeNamePolicy->isNodeNameWithUniqId(
                    $testingNodeName,
                    $nodeSource->getNode()->getNodeName()
                )
            ) {
                try {
                    if ($nodeSource->isReachable()) {
                        $oldPaths = $this->nodeMover->getNodeSourcesUrls($nodeSource->getNode());
                        $oldUpdateAt = $nodeSource->getNode()->getUpdatedAt();
                    }
                } catch (SameNodeUrlException $e) {
                    $oldPaths = [];
                }
                $alreadyUsed = $this->nodeNamePolicy->isNodeNameAlreadyUsed($testingNodeName);
                if (!$alreadyUsed) {
                    $nodeSource->getNode()->setNodeName($testingNodeName);
                } else {
                    $nodeSource->getNode()->setNodeName($this->nodeNamePolicy->getSafeNodeName($nodeSource));
                }

                /*
                 * Dispatch event
                 */
                if (isset($oldPaths) && isset($oldUpdateAt) && count($oldPaths) > 0) {
                    $dispatcher->dispatch(new NodePathChangedEvent($nodeSource->getNode(), $oldPaths, $oldUpdateAt));
                }
                $dispatcher->dispatch(new NodeUpdatedEvent($nodeSource->getNode()));
            } else {
                $this->logger->debug('Node name has not be changed.');
            }
        }
    }
}
