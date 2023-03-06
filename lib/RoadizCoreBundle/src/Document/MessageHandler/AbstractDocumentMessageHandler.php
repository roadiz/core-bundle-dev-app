<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

abstract class AbstractDocumentMessageHandler implements MessageHandlerInterface
{
    protected ManagerRegistry $managerRegistry;
    protected LoggerInterface $logger;
    protected FilesystemOperator $documentsStorage;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param LoggerInterface $messengerLogger
     * @param FilesystemOperator $documentsStorage
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        LoggerInterface $messengerLogger,
        FilesystemOperator $documentsStorage
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->logger = $messengerLogger;
        $this->documentsStorage = $documentsStorage;
    }

    abstract protected function supports(DocumentInterface $document): bool;

    abstract protected function processMessage(AbstractDocumentMessage $message, DocumentInterface $document): void;

    public function __invoke(AbstractDocumentMessage $message): void
    {
        $document = $this->managerRegistry
            ->getRepository(DocumentInterface::class)
            ->find($message->getDocumentId());

        if ($document instanceof DocumentInterface && $this->supports($document)) {
            try {
                $this->processMessage($message, $document);
                $this->managerRegistry->getManager()->flush();
            } catch (FilesystemException $exception) {
                throw new RecoverableMessageHandlingException($exception->getMessage());
            }
        }
    }
}
