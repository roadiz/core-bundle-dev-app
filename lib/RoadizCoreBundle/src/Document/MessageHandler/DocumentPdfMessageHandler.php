<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Document\DocumentFactory;
use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\Documents\Events\DocumentCreatedEvent;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\HasThumbnailInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class DocumentPdfMessageHandler extends AbstractLockingDocumentMessageHandler
{
    public function __construct(
        private readonly DocumentFactory $documentFactory,
        private readonly EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $managerRegistry,
        LoggerInterface $messengerLogger,
        FilesystemOperator $documentsStorage,
    ) {
        parent::__construct($managerRegistry, $messengerLogger, $documentsStorage);
    }

    #[\Override]
    protected function supports(DocumentInterface $document): bool
    {
        return $document->isLocal()
            && $document->isPdf()
            && \class_exists('\Imagick')
            && \class_exists('\ImagickException');
    }

    #[\Override]
    protected function processMessage(AbstractDocumentMessage $message, DocumentInterface $document): void
    {
        /*
         * This process requires document files to be locally stored!
         */
        $pdfPath = \tempnam(\sys_get_temp_dir(), 'pdf_');
        if (false === $pdfPath) {
            throw new UnrecoverableMessageHandlingException('Cannot create temporary file for PDF thumbnail.');
        }
        \rename($pdfPath, $pdfPath .= $document->getFilename());

        /*
        * Copy AV locally
        */
        $pdfPathResource = \fopen($pdfPath, 'w');
        if (false === $pdfPathResource) {
            throw new UnrecoverableMessageHandlingException('Cannot open temporary file for PDF thumbnail.');
        }
        \stream_copy_to_stream($this->documentsStorage->readStream($document->getMountPath()), $pdfPathResource);
        \fclose($pdfPathResource);

        $this->extractPdfThumbnail($document, $pdfPath);

        /*
         * Then delete local AV file
         */
        \unlink($pdfPath);
    }

    protected function extractPdfThumbnail(DocumentInterface $document, string $localPdfPath): void
    {
        if (!$document->isPdf() || !\class_exists('\Imagick') || !\class_exists('\ImagickException')) {
            return;
        }

        $thumbnailPath = \tempnam(\sys_get_temp_dir(), 'thumbnail_');
        if (false === $thumbnailPath) {
            throw new UnrecoverableMessageHandlingException('Cannot create temporary file for PDF thumbnail.');
        }
        \rename($thumbnailPath, $thumbnailPath .= $document->getFilename().'.jpg');

        try {
            $im = new \Imagick();
            $im->setResolution(144, 144);
            // Use [0] to get first page of PDF.
            if ($im->readImage($localPdfPath.'[0]')) {
                $im->writeImages($thumbnailPath, false);

                $thumbnailDocument = $this->documentFactory
                    ->setFolder($document->getFolders()->first() ?: null)
                    ->setFile(new File($thumbnailPath))
                    ->getDocument();
                if ($thumbnailDocument instanceof HasThumbnailInterface && $document instanceof HasThumbnailInterface) {
                    $thumbnailDocument->setOriginal($document);
                    $document->getThumbnails()->add($thumbnailDocument);
                    $this->managerRegistry->getManager()->flush();
                    $this->eventDispatcher->dispatch(new DocumentCreatedEvent($thumbnailDocument));
                }
            }
        } catch (\ImagickException $exception) {
            // Silent fail to avoid issue with message handling
            $this->messengerLogger->warning(
                sprintf(
                    'Cannot extract thumbnail from %s PDF file : %s',
                    $localPdfPath,
                    $exception->getMessage()
                )
            );
        }
    }
}
