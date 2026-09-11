<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Event\Document\DocumentTranslationUpdatedEvent;
use RZ\Roadiz\CoreBundle\Event\FilterNodeEvent;
use RZ\Roadiz\CoreBundle\Event\Folder\FolderUpdatedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeCreatedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeDeletedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeTaggedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeUndeletedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeUpdatedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeVisibilityChangedEvent;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesDeletedEvent;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesUpdatedEvent;
use RZ\Roadiz\CoreBundle\Event\Tag\TagUpdatedEvent;
use RZ\Roadiz\Documents\Events\DocumentCreatedEvent;
use RZ\Roadiz\Documents\Events\DocumentDeletedEvent;
use RZ\Roadiz\Documents\Events\DocumentInFolderEvent;
use RZ\Roadiz\Documents\Events\DocumentOutFolderEvent;
use RZ\Roadiz\Documents\Events\DocumentUpdatedEvent;
use RZ\Roadiz\Documents\Events\FilterDocumentEvent;
use RZ\Roadiz\SolrBundle\Message\SolrDeleteMessage;
use RZ\Roadiz\SolrBundle\Message\SolrReindexMessage;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Contracts\Service\ResetInterface;

#[AsDoctrineListener(event: Events::postFlush)]
final class SolariumSubscriber implements EventSubscriberInterface, ResetInterface
{
    /**
     * Indexing messages wait here until the unit of work is committed.
     *
     * Rozier dispatches its node events *before* `EntityManager::flush()` — see
     * AbstractAdminController::editAction, where that ordering is deliberate so a
     * listener can still throw. Sending an indexing message straight away is a race
     * against the commit: the async worker re-reads the entity by id and can get
     * there first, indexing the pre-flush state. That is how a freshly published
     * node-source kept `node_status_i:10` in Solr and disappeared from search.
     *
     * @var list<object>
     */
    private array $pending = [];

    public function __construct(private readonly MessageBusInterface $messageBus)
    {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            NodeUpdatedEvent::class => 'onSolariumNodeUpdate',
            'workflow.node.completed' => ['onSolariumNodeWorkflowComplete'],
            NodeVisibilityChangedEvent::class => 'onSolariumNodeUpdate',
            NodesSourcesUpdatedEvent::class => 'onSolariumSingleUpdate',
            NodesSourcesDeletedEvent::class => 'onSolariumSingleDelete',
            NodeDeletedEvent::class => 'onSolariumNodeDelete',
            NodeUndeletedEvent::class => 'onSolariumNodeUpdate',
            NodeTaggedEvent::class => 'onSolariumNodeUpdate',
            NodeCreatedEvent::class => 'onSolariumNodeUpdate',
            TagUpdatedEvent::class => 'onSolariumTagUpdate', // Possibly too greedy if lots of nodes tagged
            DocumentCreatedEvent::class => 'onSolariumDocumentUpdate',
            DocumentTranslationUpdatedEvent::class => 'onSolariumDocumentUpdate',
            DocumentInFolderEvent::class => 'onSolariumDocumentUpdate',
            DocumentOutFolderEvent::class => 'onSolariumDocumentUpdate',
            DocumentUpdatedEvent::class => 'onSolariumDocumentUpdate',
            DocumentDeletedEvent::class => 'onSolariumDocumentDelete',
            FolderUpdatedEvent::class => 'onSolariumFolderUpdate', // Possibly too greedy if lots of docs tagged
            /*
             * Last resort for the callers that dispatch *after* their own flush
             * (TranslateNodeMessageHandler does): without these the buffer would
             * wait for a flush that never comes and the source stays unindexed.
             */
            KernelEvents::TERMINATE => 'send',
            ConsoleEvents::TERMINATE => 'send',
            WorkerMessageHandledEvent::class => 'send',
            WorkerMessageFailedEvent::class => 'send',
        ];
    }

    /**
     * The ORM has committed: the worker now reads what the editor just saved.
     */
    public function postFlush(PostFlushEventArgs $event): void
    {
        $this->send();
    }

    public function send(): void
    {
        $pending = $this->pending;
        $this->pending = [];

        foreach ($pending as $message) {
            $this->messageBus->dispatch(new Envelope($message));
        }
    }

    /**
     * A worker runtime (FrankenPHP, Swoole, RoadRunner) keeps this instance alive
     * across requests, so the buffer must not outlive the one that filled it.
     * Anything still here belongs to a request that died before any of the drains
     * above — its transaction rolled back too, so there is nothing left to index.
     */
    #[\Override]
    public function reset(): void
    {
        $this->pending = [];
    }

    private function defer(object $message): void
    {
        $this->pending[] = $message;
    }

    public function onSolariumNodeWorkflowComplete(Event $event): void
    {
        $node = $event->getSubject();
        if ($node instanceof Node) {
            $this->defer(new SolrReindexMessage(Node::class, $node->getId()));
        }
    }

    /**
     * Update or create Solr document for current Node-source.
     *
     * @throws \Exception
     */
    public function onSolariumSingleUpdate(NodesSourcesUpdatedEvent $event): void
    {
        $this->defer(new SolrReindexMessage(NodesSources::class, $event->getNodeSource()->getId()));
    }

    /**
     * Delete solr document for current Node-source.
     */
    public function onSolariumSingleDelete(NodesSourcesDeletedEvent $event): void
    {
        $this->defer(new SolrDeleteMessage(NodesSources::class, $event->getNodeSource()->getId()));
    }

    /**
     * Delete solr documents for each Node sources.
     */
    public function onSolariumNodeDelete(NodeDeletedEvent $event): void
    {
        $this->defer(new SolrDeleteMessage(Node::class, $event->getNode()->getId()));
    }

    /**
     * Update or create solr documents for each Node sources.
     *
     * @throws \Exception
     */
    public function onSolariumNodeUpdate(FilterNodeEvent $event): void
    {
        $this->defer(new SolrReindexMessage(Node::class, $event->getNode()->getId()));
    }

    /**
     * Delete solr documents for each Document translation.
     */
    public function onSolariumDocumentDelete(FilterDocumentEvent $event): void
    {
        $document = $event->getDocument();
        if ($document instanceof Document) {
            $this->defer(new SolrDeleteMessage(Document::class, $document->getId()));
        }
    }

    /**
     * Update or create solr documents for each Document translation.
     *
     * @throws \Exception
     */
    public function onSolariumDocumentUpdate(FilterDocumentEvent $event): void
    {
        $document = $event->getDocument();
        if ($document instanceof Document) {
            $this->defer(new SolrReindexMessage(Document::class, $document->getId()));
        }
    }

    /**
     * Update solr documents linked to current event Tag.
     *
     * @throws \Exception
     *
     * @deprecated This can lead to a timeout if more than 500 nodes use that tag!
     */
    public function onSolariumTagUpdate(TagUpdatedEvent $event): void
    {
        $this->defer(new SolrReindexMessage(Tag::class, $event->getTag()->getId()));
    }

    /**
     * Update solr documents linked to current event Folder.
     *
     * @throws \Exception
     *
     * @deprecated This can lead to a timeout if more than 500 documents use that folder!
     */
    public function onSolariumFolderUpdate(FolderUpdatedEvent $event): void
    {
        $this->defer(new SolrReindexMessage(Folder::class, $event->getFolder()->getId()));
    }
}
