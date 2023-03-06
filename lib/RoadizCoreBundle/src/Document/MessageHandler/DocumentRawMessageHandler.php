<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\Documents\DownscaleImageManager;
use RZ\Roadiz\Documents\Models\DocumentInterface;

final class DocumentRawMessageHandler extends AbstractLockingDocumentMessageHandler
{
    private DownscaleImageManager $downscaleImageManager;

    public function __construct(
        DownscaleImageManager $downscaleImageManager,
        ManagerRegistry $managerRegistry,
        LoggerInterface $logger,
        FilesystemOperator $documentsStorage
    ) {
        parent::__construct($managerRegistry, $logger, $documentsStorage);
        $this->downscaleImageManager = $downscaleImageManager;
    }

    /**
     * @param  DocumentInterface $document
     * @return bool
     */
    protected function supports(DocumentInterface $document): bool
    {
        return $document->isLocal() && null !== $document->getRelativePath() && $document->isProcessable();
    }

    protected function processMessage(AbstractDocumentMessage $message, DocumentInterface $document): void
    {
        $this->downscaleImageManager->processAndOverrideDocument($document);
    }
}
