<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;

#[AsMessageHandler]
abstract class AbstractDocumentMessageHandler
{
    public function __construct(
        protected readonly ManagerRegistry $managerRegistry,
        protected readonly LoggerInterface $messengerLogger,
        protected readonly FilesystemOperator $documentsStorage
    ) {
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
