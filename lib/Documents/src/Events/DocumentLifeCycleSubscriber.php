<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Events;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Visibility;
use RZ\Roadiz\Documents\Exceptions\DocumentWithoutFileException;
use RZ\Roadiz\Documents\Models\DocumentInterface;

/**
 * Handle file management on document's lifecycle events.
 */
#[AsDoctrineListener(event: Events::postRemove)]
#[AsDoctrineListener(event: Events::preUpdate)]
final readonly class DocumentLifeCycleSubscriber
{
    public function __construct(private FilesystemOperator $documentsStorage)
    {
    }

    /**
     * @throws FilesystemException
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $document = $args->getObject();

        if (!$document instanceof DocumentInterface) {
            return;
        }

        if (
            $args->hasChangedField('filename')
            && is_string($args->getOldValue('filename'))
            && is_string($args->getNewValue('filename'))
            && $args->getOldValue('filename') !== ''
        ) {
            // This method must not throw any exception
            // because filename WILL change if document file is updated too.
            $this->renameDocumentFilename($document, $args);
        }
        if ($args->hasChangedField('private')) {
            if ($document->isPrivate() === true) {
                $this->makePrivate($document);
            } else {
                $this->makePublic($document);
            }
        }
    }

    private function renameDocumentFilename(DocumentInterface $document, PreUpdateEventArgs $args): void
    {
        $oldPath = $this->getDocumentMountPathForFilename($document, $args->getOldValue('filename'));
        $newPath = $this->getDocumentMountPathForFilename($document, $args->getNewValue('filename'));

        if ($oldPath === $newPath) {
            return;
        }

        if (!$this->documentsStorage->fileExists($oldPath)) {
            // Do not throw, just return
            return;
        }
        if ($this->documentsStorage->fileExists($newPath)) {
            // Do not throw, just return
            return;
        }
        /*
         * Only perform IO rename if old file exists and new path is free.
         */
        $this->documentsStorage->move($oldPath, $newPath);
    }

    private function makePublic(DocumentInterface $document): void
    {
        $this->validateDocument($document);
        $documentPublicPath = $this->getDocumentPublicPath($document);
        $documentPrivatePath = $this->getDocumentPrivatePath($document);

        if ($this->documentsStorage->fileExists($documentPrivatePath)) {
            $this->documentsStorage->move(
                $documentPrivatePath,
                $documentPublicPath
            );
            $this->documentsStorage->setVisibility($documentPublicPath, Visibility::PUBLIC);
            $this->cleanFileDirectory($this->getDocumentPrivateFolderPath($document));
        }
    }

    private function makePrivate(DocumentInterface $document): void
    {
        $this->validateDocument($document);
        $documentPublicPath = $this->getDocumentPublicPath($document);
        $documentPrivatePath = $this->getDocumentPrivatePath($document);

        if ($this->documentsStorage->fileExists($documentPublicPath)) {
            $this->documentsStorage->move(
                $documentPublicPath,
                $documentPrivatePath
            );
            $this->documentsStorage->setVisibility($documentPrivatePath, Visibility::PRIVATE);
            $this->cleanFileDirectory($this->getDocumentPublicFolderPath($document));
        }
    }

    /**
     * Unlink file after document has been deleted.
     *
     * @param PostRemoveEventArgs $args
     * @throws FilesystemException
     */
    public function postRemove(PostRemoveEventArgs $args): void
    {
        $document = $args->getObject();

        if (!$document instanceof DocumentInterface) {
            return;
        }

        try {
            $this->validateDocument($document);
            $document->setRawDocument(null);
            $documentPath = $this->getDocumentPath($document);

            if ($this->documentsStorage->fileExists($documentPath)) {
                $this->documentsStorage->delete($documentPath);
            }
            $this->cleanFileDirectory($this->getDocumentFolderPath($document));
        } catch (DocumentWithoutFileException $e) {
            // Do nothing when document does not have any file on system.
        }
    }

    /**
     * Remove document directory if there is no other file in it.
     *
     * @param string $documentFolderPath
     * @return void
     * @throws FilesystemException
     */
    private function cleanFileDirectory(string $documentFolderPath): void
    {
        if ($this->documentsStorage->directoryExists($documentFolderPath)) {
            $isDirEmpty = \count($this->documentsStorage->listContents($documentFolderPath)->toArray()) <= 0;
            if ($isDirEmpty) {
                $this->documentsStorage->deleteDirectory($documentFolderPath);
            }
        }
    }

    /**
     * @param DocumentInterface $document
     * @param string $filename
     *
     * @return string
     */
    private function getDocumentRelativePathForFilename(DocumentInterface $document, string $filename): string
    {
        $this->validateDocument($document);

        return $document->getFolder() . DIRECTORY_SEPARATOR . $filename;
    }

    /**
     * @param DocumentInterface $document
     * @param string            $filename
     *
     * @return string
     */
    private function getDocumentMountPathForFilename(DocumentInterface $document, string $filename): string
    {
        if ($document->isPrivate()) {
            return 'private://' . $this->getDocumentRelativePathForFilename($document, $filename);
        }
        return 'public://' . $this->getDocumentRelativePathForFilename($document, $filename);
    }

    /**
     * @param DocumentInterface $document
     * @return string
     */
    private function getDocumentPath(DocumentInterface $document): string
    {
        $this->validateDocument($document);

        if ($document->isPrivate()) {
            return $this->getDocumentPrivatePath($document);
        }
        return $this->getDocumentPublicPath($document);
    }

    /**
     * @param  DocumentInterface $document
     * @return string
     */
    private function getDocumentPublicPath(DocumentInterface $document): string
    {
        return 'public://' . $document->getRelativePath();
    }

    /**
     * @param  DocumentInterface $document
     * @return string
     */
    private function getDocumentPrivatePath(DocumentInterface $document): string
    {
        return 'private://' . $document->getRelativePath();
    }

    /**
     * @param  DocumentInterface $document
     * @return string
     */
    private function getDocumentFolderPath(DocumentInterface $document): string
    {
        if ($document->isPrivate()) {
            return $this->getDocumentPrivateFolderPath($document);
        }
        return $this->getDocumentPublicFolderPath($document);
    }

    /**
     * @param DocumentInterface $document
     * @return string
     */
    private function getDocumentPublicFolderPath(DocumentInterface $document): string
    {
        return 'public://' . $document->getFolder();
    }

    /**
     * @param DocumentInterface $document
     * @return string
     */
    private function getDocumentPrivateFolderPath(DocumentInterface $document): string
    {
        return 'private://' . $document->getFolder();
    }

    /**
     * @param DocumentInterface $document
     * @throws DocumentWithoutFileException
     */
    private function validateDocument(DocumentInterface $document): void
    {
        if (!$document->isLocal()) {
            throw new DocumentWithoutFileException($document);
        }
    }
}
