<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use League\Flysystem\FilesystemException;
use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\CoreBundle\Document\Message\DocumentFilesizeMessage;
use RZ\Roadiz\Documents\Models\AdvancedDocumentInterface;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: DocumentFilesizeMessage::class)]
final class DocumentFilesizeMessageHandler extends AbstractLockingDocumentMessageHandler
{
    #[\Override]
    protected function supports(DocumentInterface $document): bool
    {
        return $document->isLocal() && null !== $document->getRelativePath();
    }

    #[\Override]
    protected function processMessage(AbstractDocumentMessage $message, DocumentInterface $document): void
    {
        $mountPath = $document->getMountPath();

        if (null === $mountPath) {
            return;
        }

        if (!$document instanceof AdvancedDocumentInterface) {
            return;
        }
        try {
            $document->setFilesize($this->documentsStorage->fileSize($mountPath));
        } catch (FilesystemException $exception) {
            $this->messengerLogger->warning($exception->getMessage());
        }
    }
}
