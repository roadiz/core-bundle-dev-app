<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\CoreBundle\Document\Message\DocumentRawMessage;
use RZ\Roadiz\Documents\DownscaleImageManager;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: DocumentRawMessage::class, priority: -100)]
final class DocumentRawMessageHandler extends AbstractLockingDocumentMessageHandler
{
    public function __construct(
        private readonly DownscaleImageManager $downscaleImageManager,
        LockFactory $lockFactory,
        ManagerRegistry $managerRegistry,
        LoggerInterface $messengerLogger,
        FilesystemOperator $documentsStorage,
    ) {
        parent::__construct($lockFactory, $managerRegistry, $messengerLogger, $documentsStorage);
    }

    #[\Override]
    protected function getLockTtl(): int
    {
        return 30;
    }

    #[\Override]
    protected function isLockExclusive(): bool
    {
        return true;
    }

    #[\Override]
    protected function supports(DocumentInterface $document): bool
    {
        return $document->isLocal() && null !== $document->getRelativePath() && $document->isProcessable();
    }

    #[\Override]
    protected function processMessage(AbstractDocumentMessage $message, DocumentInterface $document): void
    {
        $this->downscaleImageManager->processAndOverrideDocument($document);
    }
}
