<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents;

use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\Constraint;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\Documents\Models\AdvancedDocumentInterface;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\FileHashInterface;

final readonly class DownscaleImageManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private FilesystemOperator $documentsStorage,
        private ImageManager $imageManager,
        private ?LoggerInterface $logger = null,
        private int $maxPixelSize = 0,
        private string $rawImageSuffix = '.raw',
    ) {
    }

    /**
     * Downscale document if needed, overriding raw document.
     */
    public function processAndOverrideDocument(?DocumentInterface $document = null): void
    {
        if (!$this->isValidDocument($document)) {
            return;
        }

        $documentPath = $document->getMountPath();
        $processedImage = $this->getProcessedImage($documentPath);

        if (null === $processedImage) {
            return;
        }

        if (null !== $this->saveProcessedDocument($document, $processedImage)) {
            $this->logDownscaling($documentPath, $document);
        }
    }

    /**
     * Downscale document if needed, keeping existing raw document.
     */
    public function processDocumentFromExistingRaw(?DocumentInterface $document = null): void
    {
        if (!$this->isValidDocument($document)) {
            return;
        }

        if (null !== $document->getRawDocument() && $document->getRawDocument()->isLocal()) {
            $documentPath = $document->getRawDocument()->getMountPath();
        } else {
            $documentPath = $document->getMountPath();
        }

        $processedImage = $this->getProcessedImage($documentPath);

        if (null === $processedImage) {
            return;
        }

        if (null !== $this->saveProcessedDocument($document, $processedImage, true)) {
            $this->logDownscaling($documentPath, $document);
        }
    }

    private function saveProcessedDocument(
        DocumentInterface $document,
        Image $processedImage,
        bool $keepExistingRaw = false,
    ): ?DocumentInterface {
        if (!$keepExistingRaw) {
            $this->removeOldRawDocument($document);
        }

        if (null === $document->getRawDocument() || !$keepExistingRaw) {
            return $this->storeNewProcessedImage($document, $processedImage);
        }

        return $this->overwriteExistingProcessedImage($document, $processedImage);
    }

    /**
     * Retrieve and process an image if necessary.
     */
    private function getProcessedImage(?string $documentPath): ?Image
    {
        if (null === $documentPath) {
            return null;
        }

        $documentStream = $this->documentsStorage->readStream($documentPath);

        return $this->resizeImageIfNeeded($this->imageManager->make($documentStream));
    }

    /**
     * Get downscaled image if size is higher than limit,
     * returns original image if lower or if image is a GIF.
     */
    private function resizeImageIfNeeded(Image $image): ?Image
    {
        if (!$this->doesImageSupportDownscaling($image)) {
            return null;
        }

        // prevent possible upsizing
        return $image->resize(
            $this->maxPixelSize,
            $this->maxPixelSize,
            function (Constraint $constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            }
        );
    }

    /**
     * Remove an old raw document if it exists.
     */
    private function removeOldRawDocument(DocumentInterface $document): void
    {
        if (null === $rawDocument = $document->getRawDocument()) {
            return;
        }
        /*
         * When document already exists with a raw doc reference.
         * We have to delete former raw document before creating a new one.
         * Keeping the same document to preserve existing relationships!!
         */
        $document->setRawDocument(null);
        /*
         * Make sure to disconnect raw document before removing it
         * not to trigger Cascade deleting.
         */
        $this->em->flush();
        $this->em->remove($rawDocument);
        $this->em->flush();
    }

    /**
     * Rename the original document as raw.
     */
    private function renameOriginalAsRaw(DocumentInterface $document, DocumentInterface $rawDocument): bool
    {
        $originalPath = $document->getMountPath();
        $rawPath = $rawDocument->getMountPath();

        if (!$originalPath || !$rawPath || !$this->documentsStorage->fileExists($originalPath)) {
            return false;
        }

        $this->documentsStorage->move($originalPath, $rawPath);

        return true;
    }

    /**
     * Store a new processed image, renaming the original as raw.
     */
    private function storeNewProcessedImage(DocumentInterface $document, Image $image): ?DocumentInterface
    {
        $rawDocument = clone $document;
        $rawDocument->setFilename($this->generateRawFilename($document->getFilename()));

        if (!$this->renameOriginalAsRaw($document, $rawDocument)) {
            return null;
        }

        $this->writeNewProcessedImage($document, $image);
        $document->setRawDocument($rawDocument);
        $this->updateDocumentImageSize($document, $image);
        $this->updateDocumentFileHash($document);

        $rawDocument->setRaw(true);
        $this->em->persist($rawDocument);
        $this->em->flush();

        return $document;
    }

    /**
     * Write the processed image to the storage.
     */
    private function writeNewProcessedImage(DocumentInterface $document, Image $image): void
    {
        $this->documentsStorage->write(
            $document->getMountPath(),
            $image->encode(null, 100)->getEncoded()
        );
    }

    /**
     * Write the processed image to the storage.
     */
    private function updateDocumentImageSize(DocumentInterface $document, Image $image): void
    {
        if (!$document instanceof AdvancedDocumentInterface) {
            return;
        }

        $document->setImageWidth($image->width());
        $document->setImageHeight($image->height());
        $mountPath = $document->getMountPath();
        if (null === $mountPath) {
            return;
        }
        $document->setFilesize($this->documentsStorage->fileSize($mountPath));
    }

    private function updateDocumentFileHash(DocumentInterface $document): void
    {
        /*
         * We need to re-hash file after being downscaled
         */
        if (
            !$document instanceof FileHashInterface
            || null === $document->getFileHashAlgorithm()
        ) {
            return;
        }

        /** @var DocumentInterface&FileHashInterface $document */
        $mountPath = $document->getMountPath();
        if (null === $mountPath) {
            return;
        }
        $document->setFileHash($this->documentsStorage->checksum(
            $mountPath,
            ['checksum_algo' => $document->getFileHashAlgorithm()]
        ));
    }

    /**
     * Overwrite an existing processed image.
     */
    private function overwriteExistingProcessedImage(DocumentInterface $document, Image $image): DocumentInterface
    {
        $this->documentsStorage->delete($document->getMountPath());
        $this->writeNewProcessedImage($document, $image);
        $this->updateDocumentImageSize($document, $image);
        $this->updateDocumentFileHash($document);

        return $document;
    }

    /**
     * Generate a raw filename.
     */
    private function generateRawFilename(string $filename): string
    {
        return preg_replace(
            '#\.(jpe?g|gif|tiff?|png|psd|webp|avif|heic|heif)$#',
            $this->rawImageSuffix.'.$1',
            $filename
        ) ?? throw new \InvalidArgumentException('Raw document filename cannot be null');
    }

    /**
     * Check if a document is valid for processing.
     */
    private function isValidDocument(?DocumentInterface $document): bool
    {
        return null !== $document && $document->isLocal() && $this->maxPixelSize > 0;
    }

    /**
     * Check if an image can be downscaled.
     */
    private function doesImageSupportDownscaling(Image $image): bool
    {
        return 'image/gif' !== $image->mime()
            && ($image->width() > $this->maxPixelSize || $image->height() > $this->maxPixelSize);
    }

    /**
     * Log a successful downscaling operation.
     */
    private function logDownscaling(string $path, ?DocumentInterface $document = null): void
    {
        $context = ['path' => $path];
        $this->logger?->info('Document has been downscaled.', $context);
    }
}
