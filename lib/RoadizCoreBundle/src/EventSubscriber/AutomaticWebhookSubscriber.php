<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Webhook;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeHandler;
use RZ\Roadiz\CoreBundle\Event\Document\DocumentTranslationUpdatedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeDeletedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeTaggedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeUpdatedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeVisibilityChangedEvent;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesDeletedEvent;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesPreUpdatedEvent;
use RZ\Roadiz\CoreBundle\Event\Tag\TagUpdatedEvent;
use RZ\Roadiz\CoreBundle\Webhook\Exception\TooManyWebhookTriggeredException;
use RZ\Roadiz\CoreBundle\Webhook\WebhookDispatcher;
use RZ\Roadiz\Documents\Events\DocumentUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

final readonly class AutomaticWebhookSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private WebhookDispatcher $webhookDispatcher,
        private ManagerRegistry $managerRegistry,
        private HandlerFactoryInterface $handlerFactory,
    ) {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.node.completed' => 'onAutomaticWebhook',
            NodeVisibilityChangedEvent::class => 'onAutomaticWebhook',
            NodesSourcesPreUpdatedEvent::class => 'onAutomaticWebhook',
            NodesSourcesDeletedEvent::class => 'onAutomaticWebhook',
            NodeUpdatedEvent::class => 'onAutomaticWebhook',
            NodeDeletedEvent::class => 'onAutomaticWebhook',
            NodeTaggedEvent::class => 'onAutomaticWebhook',
            TagUpdatedEvent::class => 'onAutomaticWebhook',
            DocumentTranslationUpdatedEvent::class => 'onAutomaticWebhook',
            DocumentUpdatedEvent::class => 'onAutomaticWebhook',
        ];
    }

    protected function isEventRelatedToNode(mixed $event): bool
    {
        return $event instanceof Event
            || $event instanceof NodeVisibilityChangedEvent
            || $event instanceof NodesSourcesPreUpdatedEvent
            || $event instanceof NodesSourcesDeletedEvent
            || $event instanceof NodeUpdatedEvent
            || $event instanceof NodeDeletedEvent
            || $event instanceof NodeTaggedEvent;
    }

    /**
     * @param Event|NodeVisibilityChangedEvent|NodesSourcesPreUpdatedEvent|NodesSourcesDeletedEvent|NodeDeletedEvent|NodeTaggedEvent|TagUpdatedEvent|DocumentTranslationUpdatedEvent|DocumentUpdatedEvent $event
     */
    public function onAutomaticWebhook(mixed $event): void
    {
        /** @var Webhook[] $webhooks */
        $webhooks = $this->managerRegistry->getRepository(Webhook::class)->findBy([
            'automatic' => true,
        ]);
        foreach ($webhooks as $webhook) {
            if (!$this->isEventRelatedToNode($event) || $this->isEventSubjectInRootNode($event, $webhook->getRootNode())) {
                /*
                 * Always Triggers automatic webhook if there is no registered root node, or
                 * event is not related to a node.
                 */
                try {
                    $this->webhookDispatcher->dispatch($webhook);
                } catch (TooManyWebhookTriggeredException) {
                    // do nothing
                }
            }
        }
    }

    private function isEventSubjectInRootNode(mixed $event, ?Node $rootNode): bool
    {
        if (null === $rootNode) {
            /*
             * If root node does not exist, subject is always in root.
             */
            return true;
        }

        switch (true) {
            case $event instanceof Event:
                $subject = $event->getSubject();
                if (!$subject instanceof Node) {
                    return false;
                }
                break;
            case $event instanceof NodeUpdatedEvent:
            case $event instanceof NodeDeletedEvent:
            case $event instanceof NodeTaggedEvent:
            case $event instanceof NodeVisibilityChangedEvent:
                $subject = $event->getNode();
                break;
            case $event instanceof NodesSourcesPreUpdatedEvent:
            case $event instanceof NodesSourcesDeletedEvent:
                $subject = $event->getNodeSource()->getNode();
                break;
            default:
                return false;
        }

        $handler = $this->handlerFactory->getHandler($subject);
        if ($handler instanceof NodeHandler) {
            return $handler->isRelatedToNode($rootNode);
        }

        return false;
    }
}
