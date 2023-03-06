<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use League\Flysystem\FilesystemException;
use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\Documents\Models\AdvancedDocumentInterface;
use RZ\Roadiz\Documents\Models\DocumentInterface;

final class DocumentFilesizeMessageHandler extends AbstractLockingDocumentMessageHandler
{
    /**
     * @param  DocumentInterface $document
     * @return bool
     */
    protected function supports(DocumentInterface $document): bool
    {
        return $document->isLocal() && null !== $document->getRelativePath();
    }

    protected function processMessage(AbstractDocumentMessage $message, DocumentInterface $document): void
    {
        if (!$document instanceof AdvancedDocumentInterface) {
            return;
        }
        try {
            $document->setFilesize($this->documentsStorage->fileSize($document->getMountPath()));
        } catch (FilesystemException $exception) {
            $this->logger->warning($exception->getMessage());
        }
    }
}
