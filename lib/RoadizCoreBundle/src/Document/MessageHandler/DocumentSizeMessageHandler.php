<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use Doctrine\Persistence\ManagerRegistry;
use Intervention\Image\Exceptions\DriverException;
use Intervention\Image\ImageManager;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\SizeableInterface;

final class DocumentSizeMessageHandler extends AbstractLockingDocumentMessageHandler
{
    public function __construct(
        private readonly ImageManager $imageManager,
        ManagerRegistry $managerRegistry,
        LoggerInterface $messengerLogger,
        FilesystemOperator $documentsStorage,
    ) {
        parent::__construct($managerRegistry, $messengerLogger, $documentsStorage);
    }

    protected function supports(DocumentInterface $document): bool
    {
        return $document->isLocal() && $document->isImage();
    }

    protected function processMessage(AbstractDocumentMessage $message, DocumentInterface $document): void
    {
        if (!$document instanceof SizeableInterface) {
            return;
        }
        try {
            $imageProcess = $this->imageManager->read($this->documentsStorage->readStream($document->getMountPath()));
            $document->setImageWidth($imageProcess->width());
            $document->setImageHeight($imageProcess->height());
        } catch (DriverException|FilesystemException $exception) {
            $this->messengerLogger->warning(
                'Document file is not a readable image.',
                [
                    'path' => $document->getMountPath(),
                    'message' => $exception->getMessage(),
                ]
            );
        }
    }
}
