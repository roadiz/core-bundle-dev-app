<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\FileHashInterface;
use RZ\Roadiz\Documents\Models\FolderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Create documents from UploadedFile.
 *
 * Factory methods do not flush, only persist in order to use it in loops.
 */
abstract class AbstractDocumentFactory
{
    private LoggerInterface $logger;
    private ?File $file = null;
    private ?FolderInterface $folder = null;
    private FilesystemOperator $documentsStorage;
    private DocumentFinderInterface $documentFinder;

    public function __construct(
        FilesystemOperator $documentsStorage,
        DocumentFinderInterface $documentFinder,
        ?LoggerInterface $logger = null,
    ) {
        if (!$documentsStorage instanceof MountManager) {
            trigger_error('Document Storage must be a MountManager to address public and private files.', E_USER_WARNING);
        }
        $this->documentsStorage = $documentsStorage;
        $this->documentFinder = $documentFinder;
        $this->logger = $logger ?? new NullLogger();
    }

    public function getFile(): File
    {
        if (null === $this->file) {
            throw new \BadMethodCallException('File should be defined before using it.');
        }

        return $this->file;
    }

    /**
     * @return $this
     */
    public function setFile(File $file): static
    {
        $this->file = $file;

        return $this;
    }

    public function getFolder(): ?FolderInterface
    {
        return $this->folder;
    }

    /**
     * @return $this
     */
    public function setFolder(?FolderInterface $folder = null): static
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * Special case for SVG without XML statement.
     */
    protected function parseSvgMimeType(DocumentInterface $document): void
    {
        if (
            ('text/plain' === $document->getMimeType() || 'text/html' === $document->getMimeType())
            && preg_match('#\.svg$#', $document->getFilename())
        ) {
            $this->logger->debug('Uploaded a SVG without xml declaration. Presuming it’s a valid SVG file.');
            $document->setMimeType('image/svg+xml');
        }
    }

    abstract protected function createDocument(): DocumentInterface;

    abstract protected function persistDocument(DocumentInterface $document): void;

    protected function getHashAlgorithm(): string
    {
        return 'sha256';
    }

    /**
     * Create a document from UploadedFile, Be careful, this method does not flush, only
     * persists current Document.
     *
     * @param bool $allowEmpty      Default false, requires a local file to create new document entity
     * @param bool $allowDuplicates Default false, always import new document even if file already exists
     *
     * @throws FilesystemException
     */
    public function getDocument(bool $allowEmpty = false, bool $allowDuplicates = false): ?DocumentInterface
    {
        if (false === $allowEmpty) {
            // Getter throw exception on null file
            $file = $this->getFile();
        } else {
            $file = $this->file;
        }

        if (null === $file) {
            return null;
        }

        if ($file instanceof UploadedFile && !$file->isValid()) {
            return null;
        }

        $fileHash = hash_file($this->getHashAlgorithm(), $file->getPathname());

        /*
         * Serve already existing Document
         */
        if (false !== $fileHash && !$allowDuplicates) {
            $existingDocument = $this->documentFinder->findOneByHashAndAlgorithm($fileHash, $this->getHashAlgorithm());
            if (null !== $existingDocument) {
                if (
                    $existingDocument->isRaw()
                    && null !== $existingDownscaledDocument = $existingDocument->getDownscaledDocument()
                ) {
                    $existingDocument = $existingDownscaledDocument;
                }
                if (null !== $this->folder) {
                    $existingDocument->addFolder($this->folder);
                    $this->folder->addDocument($existingDocument);
                }
                $this->logger->info(sprintf(
                    'File %s already exists with same checksum, do not upload it twice.',
                    $existingDocument->getFilename()
                ));

                return $existingDocument;
            }
        }

        $document = $this->createDocument();
        $document->setFilename($this->getFileName());
        if ($file instanceof UploadedFile) {
            $document->setMimeType($file->getClientMimeType());
        } else {
            $document->setMimeType($file->getMimeType() ?? '');
        }

        $this->parseSvgMimeType($document);

        if (
            $document instanceof FileHashInterface
            && false !== $fileHash
        ) {
            $document->setFileHash($fileHash);
            $document->setFileHashAlgorithm($this->getHashAlgorithm());
        }

        $this->moveFile($file, $document);
        $this->persistDocument($document);

        if (null !== $this->folder) {
            $document->addFolder($this->folder);
            $this->folder->addDocument($document);
        }

        return $document;
    }

    /**
     * Updates a document from UploadedFile, Be careful, this method does not flush.
     *
     * @throws FilesystemException
     */
    public function updateDocument(DocumentInterface $document): DocumentInterface
    {
        $file = $this->getFile();

        if (
            $file instanceof UploadedFile
            && !$file->isValid()
        ) {
            return $document;
        }

        if ($document->isLocal() && null !== $mountPath = $document->getMountPath()) {
            /*
             * In case file already exists
             */
            if ($this->documentsStorage->fileExists($mountPath)) {
                $this->documentsStorage->delete($mountPath);
            }
        }

        if (DownloadedFile::sanitizeFilename($this->getFileName()) == $document->getFilename()) {
            $previousFolder = $document->getMountFolderPath();

            if (null !== $previousFolder && $this->documentsStorage->directoryExists($previousFolder)) {
                $hasFiles = \count($this->documentsStorage->listContents($previousFolder)->toArray()) > 0;
                // Remove previous folder if it's empty
                if (!$hasFiles) {
                    $this->documentsStorage->deleteDirectory($previousFolder);
                }
            }

            $document->setFolder(\mb_substr(hash('crc32b', date('YmdHi')), 0, 12));
        }

        $document->setFilename($this->getFileName());
        if ($file instanceof UploadedFile) {
            $document->setMimeType($file->getClientMimeType());
        } else {
            $document->setMimeType($file->getMimeType() ?? '');
        }
        $this->parseSvgMimeType($document);
        $this->moveFile($file, $document);

        return $document;
    }

    /**
     * @throws FilesystemException
     */
    public function moveFile(File $localFile, DocumentInterface $document): void
    {
        if (null !== $document->getMountPath()) {
            $stream = fopen($localFile->getPathname(), 'r');
            $this->documentsStorage->writeStream(
                $document->getMountPath(),
                $stream
            );
            if (is_resource($stream)) {
                fclose($stream);
            }
            (new Filesystem())->remove($localFile->getPathname());
        }
    }

    protected function getFileName(): string
    {
        $file = $this->getFile();

        if ($file instanceof UploadedFile) {
            $fileName = $file->getClientOriginalName();
        } elseif (
            $file instanceof DownloadedFile
            && null !== $file->getOriginalFilename()
            && '' !== $file->getOriginalFilename()
        ) {
            $fileName = $file->getOriginalFilename();
        } else {
            $fileName = $file->getFilename();
        }

        return $fileName;
    }

    /**
     * Create a Document from an external URL.
     *
     * @throws FilesystemException
     */
    public function getDocumentFromUrl(string $downloadUrl): ?DocumentInterface
    {
        $downloadedFile = DownloadedFile::fromUrl($downloadUrl);
        if (null !== $downloadedFile) {
            return $this->setFile($downloadedFile)->getDocument();
        }

        return null;
    }
}
