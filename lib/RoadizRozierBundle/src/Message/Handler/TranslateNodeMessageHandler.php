<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Message\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesUpdatedEvent;
use RZ\Roadiz\CoreBundle\Node\NodeTranslator;
use RZ\Roadiz\CoreBundle\Repository\AllStatusesNodeRepository;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\RozierBundle\Message\TranslateNodeMessage;
use RZ\Roadiz\RozierBundle\TranslateAssistant\Exception\TranslateAssistantException;
use RZ\Roadiz\RozierBundle\TranslateAssistant\Exception\TranslateAssistantTransportException;
use RZ\Roadiz\RozierBundle\TranslateAssistant\Exception\TranslateAssistantUsageException;
use RZ\Roadiz\RozierBundle\TranslateAssistant\NodesSourcesTranslator;
use RZ\Roadiz\RozierBundle\TranslateAssistant\NullTranslateAssistant;
use RZ\Roadiz\RozierBundle\TranslateAssistant\TranslateAssistantInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
final readonly class TranslateNodeMessageHandler
{
    public function __construct(
        private NodeTranslator $nodeTranslator,
        private NodesSourcesTranslator $nodesSourcesTranslator,
        private TranslateAssistantInterface $translateAssistant,
        private AllStatusesNodeRepository $allStatusesNodeRepository,
        private ManagerRegistry $managerRegistry,
        private NodeTypes $nodeTypesBag,
        private MessageBusInterface $bus,
        private EventDispatcherInterface $dispatcher,
        private LogTrail $logTrail,
        private TranslatorInterface $translator,
    ) {
    }

    public function __invoke(TranslateNodeMessage $message): void
    {
        if ($this->translateAssistant instanceof NullTranslateAssistant) {
            throw new UnrecoverableMessageHandlingException('No translate-assistant provider is configured');
        }

        /** @var Node|null $node */
        $node = $this->allStatusesNodeRepository->find($message->getNodeId());
        if (null === $node) {
            throw new UnrecoverableMessageHandlingException('Node does not exist');
        }

        $entityManager = $this->managerRegistry->getManagerForClass(NodesSources::class);
        if (!$entityManager instanceof EntityManagerInterface) {
            throw new UnrecoverableMessageHandlingException('No entity manager for NodesSources');
        }

        $sourceTranslation = $entityManager->find(Translation::class, $message->getSourceTranslationId());
        $destinationTranslation = $entityManager->find(Translation::class, $message->getDestinationTranslationId());
        if (!$sourceTranslation instanceof Translation || !$destinationTranslation instanceof Translation) {
            throw new UnrecoverableMessageHandlingException('Translation does not exist');
        }

        // Never recurse inside NodeTranslator: children get their own message, so a failing
        // child cannot roll back its siblings.
        $this->nodeTranslator->translateNode($sourceTranslation, $destinationTranslation, $node, false);

        // ponytail: assumes nothing else persists NodesSources during this handler. Only
        // NodesSourcesUrlsCacheEventSubscriber listens to NodesSourcesCreatedEvent, and it
        // only purges a cache.
        $created = array_filter(
            $entityManager->getUnitOfWork()->getScheduledEntityInsertions(),
            fn (object $entity) => $entity instanceof NodesSources
        );

        // Already translated: nothing was scheduled, do not flush.
        if ([] !== $created) {
            try {
                $this->translateCreatedSources($created, $entityManager, $sourceTranslation, $destinationTranslation);
            } catch (TranslateAssistantException $exception) {
                // Nothing was flushed for this node, so there is no partial source to clean up.
                throw $this->asMessengerFailure($exception, $node);
            }
        }

        if ($message->isTranslateChildren()) {
            /** @var Node $child */
            foreach ($node->getChildren() as $child) {
                $this->bus->dispatch(new TranslateNodeMessage(
                    $child->getId(),
                    $sourceTranslation->getId(),
                    $destinationTranslation->getId(),
                    true,
                ));
            }
        }
    }

    /**
     * @param array<NodesSources> $created
     */
    private function translateCreatedSources(
        array $created,
        EntityManagerInterface $entityManager,
        Translation $sourceTranslation,
        Translation $destinationTranslation,
    ): void {
        foreach ($created as $source) {
            $fields = $this->nodeTypesBag->get($source->getNodeTypeName())?->getFields();
            if (null === $fields) {
                throw new UnrecoverableMessageHandlingException(sprintf('Node-type "%s" does not exist', $source->getNodeTypeName()));
            }
            $this->nodesSourcesTranslator->translate(
                $source,
                $fields,
                $sourceTranslation->getLocale(),
                $destinationTranslation->getLocale(),
            );
        }

        $entityManager->flush();

        /*
         * SolariumSubscriber listens to NodesSourcesUpdatedEvent, not NodesSourcesCreatedEvent:
         * without this dispatch the freshly translated source is never indexed.
         */
        foreach ($created as $source) {
            $this->dispatcher->dispatch(new NodesSourcesUpdatedEvent($source));
        }
    }

    /**
     * Decides, from the exception type alone, whether Messenger should try again — and warns the
     * editor either way.
     *
     * There is no Request in a worker, so LogTrail writes to the log table only (no flash): that
     * is what makes the warning reachable from the back-office history.
     */
    private function asMessengerFailure(TranslateAssistantException $exception, Node $node): \Throwable
    {
        $retryable = $exception instanceof TranslateAssistantTransportException;

        $this->logTrail->publishErrorMessage(
            null,
            $this->translator->trans(
                $retryable
                    ? 'translate_assistant.error.provider_unreachable'
                    : ($exception instanceof TranslateAssistantUsageException
                        ? 'translate_assistant.error.quota_exceeded'
                        : 'translate_assistant.error.check_integration'),
                ['%name%' => $node->getNodeName()]
            ),
            $node
        );

        // Quota and credential failures cannot heal within a retry window, and each attempt is
        // billed: stop now instead of burning the retries.
        return $retryable
            ? $exception
            : new UnrecoverableMessageHandlingException($exception->getMessage(), previous: $exception);
    }
}
