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
use RZ\Roadiz\CoreBundle\Document\Message\DocumentSizeMessage;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\SizeableInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: DocumentSizeMessage::class)]
final class DocumentSizeMessageHandler extends AbstractLockingDocumentMessageHandler
{
    public function __construct(
        private readonly ImageManager $imageManager,
        LockFactory $lockFactory,
        ManagerRegistry $managerRegistry,
        LoggerInterface $messengerLogger,
        FilesystemOperator $documentsStorage,
    ) {
        parent::__construct($lockFactory, $managerRegistry, $messengerLogger, $documentsStorage);
    }

    #[\Override]
    protected function supports(DocumentInterface $document): bool
    {
        return $document->isLocal() && $document->isImage() && !$document->isSvg();
    }

    #[\Override]
    protected function processMessage(AbstractDocumentMessage $message, DocumentInterface $document): void
    {
        $mountPath = $document->getMountPath();

        if (null === $mountPath) {
            return;
        }

        if (!$document instanceof SizeableInterface) {
            return;
        }

        // If media size is already set, do nothing.
        if ($document->getImageWidth() > 0 && $document->getImageHeight() > 0) {
            return;
        }

        try {
            $imageProcess = $this->imageManager->read($this->documentsStorage->readStream($mountPath));
            $document->setImageWidth($imageProcess->width());
            $document->setImageHeight($imageProcess->height());
        } catch (DriverException|FilesystemException $exception) {
            $this->messengerLogger->warning(
                'Document file is not a readable image.',
                [
                    'path' => $mountPath,
                    'message' => $exception->getMessage(),
                ]
            );
        }
    }
}
