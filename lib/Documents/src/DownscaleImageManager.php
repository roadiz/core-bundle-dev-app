<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents;

use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\Constraint;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\FileHashInterface;

final class DownscaleImageManager
{
    protected EntityManagerInterface $em;
    protected ?LoggerInterface $logger;
    protected int $maxPixelSize = 0;
    protected string $rawImageSuffix = ".raw";
    protected ImageManager $imageManager;
    private FilesystemOperator $documentsStorage;

    public function __construct(
        EntityManagerInterface $em,
        FilesystemOperator $documentsStorage,
        ImageManager $imageManager,
        ?LoggerInterface $logger = null,
        int $maxPixelSize = 0,
        string $rawImageSuffix = ".raw"
    ) {
        $this->maxPixelSize = $maxPixelSize;
        $this->rawImageSuffix = $rawImageSuffix;
        $this->em = $em;
        $this->logger = $logger;
        $this->imageManager = $imageManager;
        $this->documentsStorage = $documentsStorage;
    }

    /**
     * Downscale document if needed, overriding raw document.
     *
     * @param DocumentInterface|null $document
     * @throws FilesystemException
     */
    public function processAndOverrideDocument(?DocumentInterface $document = null): void
    {
        if (null !== $document && $document->isLocal() && $this->maxPixelSize > 0) {
            $mountPath = $document->getMountPath();
            if (null === $mountPath) {
                return;
            }
            $processImage = $this->getDownscaledImage($this->imageManager->make(
                $this->documentsStorage->readStream($mountPath)
            ));
            if (false !== $processImage) {
                if (
                    null !== $this->createDocumentFromImage($document, $processImage)
                    && null !== $this->logger
                ) {
                    $this->logger->info(
                        'Document has been downscaled.',
                        [
                            'path' => $mountPath
                        ]
                    );
                }
            }
        }
    }

    /**
     * Downscale document if needed, keeping existing raw document.
     *
     * @param DocumentInterface|null $document
     * @throws FilesystemException
     */
    public function processDocumentFromExistingRaw(?DocumentInterface $document = null): void
    {
        if (null !== $document && $document->isLocal() && $this->maxPixelSize > 0) {
            if (null !== $document->getRawDocument() && $document->getRawDocument()->isLocal()) {
                $documentPath = $document->getRawDocument()->getMountPath();
            } else {
                $documentPath = $document->getMountPath();
            }

            if (null === $documentPath) {
                return;
            }

            $documentStream = $this->documentsStorage->readStream($documentPath);

            if (false !== $processImage = $this->getDownscaledImage($this->imageManager->make($documentStream))) {
                if (
                    null !== $this->createDocumentFromImage($document, $processImage, true)
                    && null !== $this->logger
                ) {
                    $this->logger->info('Document has been downscaled.', ['path' => $documentPath, 'entity' => $document]);
                }
            }
        }
    }

    /**
     * Get downscaled image if size is higher than limit,
     * returns original image if lower or if image is a GIF.
     *
     * @param  Image $processImage
     * @return Image|null
     */
    protected function getDownscaledImage(Image $processImage): ?Image
    {
        if (
            $processImage->mime() !== 'image/gif'
            && ($processImage->width() > $this->maxPixelSize || $processImage->height() > $this->maxPixelSize)
        ) {
            // prevent possible upsizing
            $processImage->resize(
                $this->maxPixelSize,
                $this->maxPixelSize,
                function (Constraint $constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                }
            );
            return $processImage;
        }
        return null;
    }

    /**
     * @param DocumentInterface $document
     * @return void
     */
    protected function updateDocumentFileHash(DocumentInterface $document): void
    {
        /*
         * We need to re-hash file after being downscaled
         */
        if (
            $document instanceof FileHashInterface &&
            null !== $document->getFileHashAlgorithm()
        ) {
            /** @var DocumentInterface & FileHashInterface $document */
            $mountPath = $document->getMountPath();
            if (null === $mountPath) {
                return;
            }
            $document->setFileHash($this->documentsStorage->checksum(
                $mountPath,
                ['checksum_algo' => $document->getFileHashAlgorithm()]
            ));
        }
    }

    /**
     * @param DocumentInterface $originalDocument
     * @param Image|null $processImage
     * @param bool $keepExistingRaw
     * @return DocumentInterface|null
     * @throws FilesystemException
     */
    protected function createDocumentFromImage(
        DocumentInterface $originalDocument,
        Image $processImage = null,
        bool $keepExistingRaw = false
    ): ?DocumentInterface {
        if (
            false === $keepExistingRaw &&
            null !== $formerRawDoc = $originalDocument->getRawDocument()
        ) {
            /*
             * When document already exists with a raw doc reference.
             * We have to delete former raw document before creating a new one.
             * Keeping the same document to preserve existing relationships!!
             */
            $originalDocument->setRawDocument(null);
            /*
             * Make sure to disconnect raw document before removing it
             * not to trigger Cascade deleting.
             */
            $this->em->flush();
            $this->em->remove($formerRawDoc);
            $this->em->flush();
        }

        if (null === $originalDocument->getRawDocument() || $keepExistingRaw === false) {
            if (null === $processImage) {
                return $originalDocument;
            }
            /*
             * We clone it to host raw document.
             * Keeping the same document to preserve existing relationships!!
             *
             * Get every data from raw document.
             */
            $rawDocument = clone $originalDocument;
            $rawDocumentName = preg_replace(
                '#\.(jpe?g|gif|tiff?|png|psd|webp|avif|heic|heif)$#',
                $this->rawImageSuffix . '.$1',
                $originalDocument->getFilename()
            );
            if (null === $rawDocumentName) {
                throw new \InvalidArgumentException('Raw document filename cannot be null');
            }
            $rawDocument->setFilename($rawDocumentName);
            $originalDocumentPath = $originalDocument->getMountPath();
            $rawDocumentPath = $rawDocument->getMountPath();

            if (
                null !== $originalDocumentPath &&
                null !== $rawDocumentPath &&
                $this->documentsStorage->fileExists($originalDocumentPath) &&
                !$this->documentsStorage->fileExists($rawDocumentPath)
            ) {
                /*
                 * Original document path becomes raw document path. Rename it.
                 */
                $this->documentsStorage->move($originalDocumentPath, $rawDocumentPath);
                /*
                 * Then save downscaled image as original document path.
                 */
                $this->documentsStorage->write(
                    $originalDocumentPath,
                    $processImage->encode(null, 100)->getEncoded()
                );
                $originalDocument->setRawDocument($rawDocument);

                /*
                 * We need to re-hash file after being downscaled
                 */
                $this->updateDocumentFileHash($originalDocument);
                $rawDocument->setRaw(true);

                $this->em->persist($rawDocument);
                $this->em->flush();

                return $originalDocument;
            }
            return null;
        } elseif (null !== $processImage) {
            /*
             * New downscale document has been generated, we keep existing RAW document
             * but we override downscaled file with the new one.
             */
            $originalDocumentPath = $originalDocument->getMountPath();
            if (null === $originalDocumentPath) {
                return null;
            }
            /*
             * Remove existing downscaled document.
             */
            $this->documentsStorage->delete($originalDocumentPath);
            /*
             * Then save downscaled image as original document path.
             */
            $this->documentsStorage->write(
                $originalDocumentPath,
                $processImage->encode(null, 100)->getEncoded()
            );
            /*
             * We need to re-hash file after being downscaled
             */
            $this->updateDocumentFileHash($originalDocument);
            $this->em->flush();

            return $originalDocument;
        } else {
            /*
             * If raw document size is inside new maxSize cap
             * we delete it and use it as new active document file.
             */
            $rawDocument = $originalDocument->getRawDocument();
            if (null !== $rawDocument) {
                $originalDocumentPath = $originalDocument->getMountPath();
                $rawDocumentPath = $rawDocument->getMountPath();

                if (null === $originalDocumentPath || null === $rawDocumentPath) {
                    return null;
                }

                /*
                 * Remove existing downscaled document.
                 */
                $this->documentsStorage->delete($originalDocumentPath);
                $this->documentsStorage->move(
                    $rawDocumentPath,
                    $originalDocumentPath
                );

                /*
                 * Remove Raw document
                 */
                $originalDocument->setRawDocument(null);
                /*
                 * We need to re-hash file after being downscaled
                 */
                $this->updateDocumentFileHash($originalDocument);
                /*
                 * Make sure to disconnect raw document before removing it
                 * not to trigger Cascade deleting.
                 */
                $this->em->flush();
                $this->em->remove($rawDocument);
                $this->em->flush();
            }

            return $originalDocument;
        }
    }
}
