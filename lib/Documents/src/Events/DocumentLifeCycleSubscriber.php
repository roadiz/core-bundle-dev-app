<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Events;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\Visibility;
use RZ\Roadiz\Documents\Exceptions\DocumentWithoutFileException;
use RZ\Roadiz\Documents\Models\DocumentInterface;

/**
 * Handle file management on document's lifecycle events.
 */
class DocumentLifeCycleSubscriber implements EventSubscriber
{
    private FilesystemOperator $documentsStorage;

    public function __construct(FilesystemOperator $documentsStorage)
    {
        $this->documentsStorage = $documentsStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents(): array
    {
        return array(
            Events::postRemove,
            Events::preUpdate,
        );
    }

    /**
     * @param PreUpdateEventArgs $args
     * @throws FilesystemException
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $document = $args->getObject();
        if (
            $document instanceof DocumentInterface
            && $args->hasChangedField('filename')
            && is_string($args->getOldValue('filename'))
            && is_string($args->getNewValue('filename'))
            && $args->getOldValue('filename') !== ''
        ) {
            $oldPath = $this->getDocumentMountPathForFilename($document, $args->getOldValue('filename'));
            $newPath = $this->getDocumentMountPathForFilename($document, $args->getNewValue('filename'));

            if ($oldPath !== $newPath) {
                if ($this->documentsStorage->fileExists($oldPath) && !$this->documentsStorage->fileExists($newPath)) {
                    /*
                     * Only perform IO rename if old file exists and new path is free.
                     */
                    $this->documentsStorage->move($oldPath, $newPath);
                } else {
                    throw new UnableToMoveFile('Cannot rename file from ' . $oldPath . ' to ' . $newPath);
                }
            }
        }
        if ($document instanceof DocumentInterface && $args->hasChangedField('private')) {
            if ($document->isPrivate() === true) {
                $this->makePrivate($document, $args);
            } else {
                $this->makePublic($document, $args);
            }
        }
    }

    /**
     * @param DocumentInterface $document
     * @param PreUpdateEventArgs $args
     * @throws FilesystemException
     */
    protected function makePublic(DocumentInterface $document, PreUpdateEventArgs $args): void
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

    /**
     * @param DocumentInterface $document
     * @param PreUpdateEventArgs $args
     * @throws FilesystemException
     */
    protected function makePrivate(DocumentInterface $document, PreUpdateEventArgs $args): void
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
     * @param LifecycleEventArgs $args
     * @throws FilesystemException
     */
    public function postRemove(LifecycleEventArgs $args): void
    {
        $document = $args->getObject();
        if ($document instanceof DocumentInterface) {
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
    }

    /**
     * Remove document directory if there is no other file in it.
     *
     * @param string $documentFolderPath
     * @return void
     * @throws FilesystemException
     */
    protected function cleanFileDirectory(string $documentFolderPath): void
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
    protected function getDocumentRelativePathForFilename(DocumentInterface $document, string $filename): string
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
    protected function getDocumentMountPathForFilename(DocumentInterface $document, string $filename): string
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
    protected function getDocumentPath(DocumentInterface $document): string
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
    protected function getDocumentPublicPath(DocumentInterface $document): string
    {
        return 'public://' . $document->getRelativePath();
    }

    /**
     * @param  DocumentInterface $document
     * @return string
     */
    protected function getDocumentPrivatePath(DocumentInterface $document): string
    {
        return 'private://' . $document->getRelativePath();
    }

    /**
     * @param  DocumentInterface $document
     * @return string
     */
    protected function getDocumentFolderPath(DocumentInterface $document): string
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
    protected function getDocumentPublicFolderPath(DocumentInterface $document): string
    {
        return 'public://' . $document->getFolder();
    }

    /**
     * @param DocumentInterface $document
     * @return string
     */
    protected function getDocumentPrivateFolderPath(DocumentInterface $document): string
    {
        return 'private://' . $document->getFolder();
    }

    /**
     * @param DocumentInterface $document
     * @throws DocumentWithoutFileException
     */
    protected function validateDocument(DocumentInterface $document): void
    {
        if (!$document->isLocal()) {
            throw new DocumentWithoutFileException($document);
        }
    }
}
