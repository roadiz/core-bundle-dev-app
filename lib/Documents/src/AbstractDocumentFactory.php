<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents;

use enshrined\svgSanitize\Sanitizer;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\Documents\Exceptions\DocumentTypeNotAllowedException;
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
    private ?File $file = null;
    private ?FolderInterface $folder = null;

    public function __construct(
        protected readonly FilesystemOperator $documentsStorage,
        protected readonly DocumentFinderInterface $documentFinder,
        protected readonly LoggerInterface $logger,
    ) {
        if (!$documentsStorage instanceof MountManager) {
            trigger_error('Document Storage must be a MountManager to address public and private files.', E_USER_WARNING);
        }
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
     * Web-executable file extensions that must never be stored, whatever their declared MIME type.
     *
     * SVG is intentionally excluded: it is a legitimate image type. Its content is
     * sanitized synchronously by sanitizeSvgFileIfNeeded() before storage, and reprocessed
     * asynchronously by DocumentSvgMessageHandler afterward.
     *
     * @return string[]
     */
    protected function getForbiddenFileExtensions(): array
    {
        return [
            // Server-interpreted
            'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phps', 'phtml', 'phtm', 'pht', 'phar', 'phpt',
            'cgi', 'pl', 'py', 'rb', 'sh', 'bash',
            'asp', 'aspx', 'asa', 'asax', 'ascx', 'ashx', 'asmx', 'cer',
            'jsp', 'jspx', 'jsw', 'jsv', 'jspf',
            'jhtml', 'shtml', 'shtm', 'stm',
            'htaccess', 'htpasswd',
            // Browser-executable
            'html', 'htm', 'xhtml', 'xht', 'hta', 'mht', 'mhtml', 'js', 'mjs', 'swf',
        ];
    }

    /**
     * Checks every dot-separated segment of the filename against the forbidden extensions,
     * so double extensions (e.g. "shell.php.jpg") and leading-dot files (e.g. ".htaccess") are caught.
     */
    public function isFilenameAllowed(string $filename): bool
    {
        return null === $this->findForbiddenExtension($filename);
    }

    /**
     * @return string|null The forbidden segment that matched, or null if none did
     */
    private function findForbiddenExtension(string $filename): ?string
    {
        $forbidden = array_map(strtolower(...), $this->getForbiddenFileExtensions());
        $segments = explode('.', strtolower($filename));

        foreach ($segments as $segment) {
            if ('' !== $segment && \in_array($segment, $forbidden, true)) {
                return $segment;
            }
        }

        return null;
    }

    /**
     * @throws DocumentTypeNotAllowedException
     */
    private function assertFileTypeIsAllowed(string $filename): void
    {
        $forbiddenExtension = $this->findForbiddenExtension($filename);
        if (null !== $forbiddenExtension) {
            throw new DocumentTypeNotAllowedException($filename, $forbiddenExtension);
        }
    }

    /**
     * Sanitize SVG file content on disk before it is moved to its final storage location.
     *
     * SVG cannot be denylisted by extension (legitimate image type), but it can carry
     * inline <script>/event-handler XSS. Declared MIME is attacker-controlled, so this
     * also triggers on a ".svg" filename regardless of the declared MIME type. This runs
     * synchronously (unlike the async DocumentSvgMessageHandler triggered after storage)
     * so no unsanitized SVG is ever servable, even briefly.
     */
    private function sanitizeSvgFileIfNeeded(File $file, DocumentInterface $document): void
    {
        $isSvg = 'image/svg+xml' === $document->getMimeType()
            || 'svg' === strtolower(pathinfo($this->getFileName(), PATHINFO_EXTENSION));

        if (!$isSvg) {
            return;
        }

        $dirtySvg = file_get_contents($file->getPathname());
        if (false === $dirtySvg) {
            return;
        }

        $sanitizer = new Sanitizer();
        $sanitizer->minify(true);
        $cleanSvg = $sanitizer->sanitize($dirtySvg);

        if (false === $cleanSvg) {
            throw new \RuntimeException(sprintf('SVG file "%s" could not be sanitized.', $this->getFileName()));
        }

        file_put_contents($file->getPathname(), $cleanSvg);
        $document->setMimeType('image/svg+xml');
    }

    /**
     * Create a document from UploadedFile, Be careful, this method does not flush, only
     * persists current Document.
     *
     * @param bool $allowEmpty      Default false, requires a local file to create new document entity
     * @param bool $allowDuplicates Default false, always import new document even if file already exists
     *
     * @throws FilesystemException
     * @throws DocumentTypeNotAllowedException
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
            $document = $this->createDocument();
            $this->persistDocument($document);

            return $document;
        }

        if ($file instanceof UploadedFile && !$file->isValid()) {
            return null;
        }

        $this->assertFileTypeIsAllowed($this->getFileName());

        $fileHash = hash_file($this->getHashAlgorithm(), $file->getPathname());

        /*
         * Serve already existing Document
         */
        if (false !== $fileHash && !$allowDuplicates) {
            $existingDocument = $this->documentFinder->findOneByHashAndAlgorithm($fileHash, $this->getHashAlgorithm());
            if (null !== $existingDocument) {
                /*
                 * If existing document is a RAW, serve its downscaled version
                 */
                if (null !== $existingDownscaledDocument = $existingDocument->getDownscaledDocument()) {
                    $existingDocument = $existingDownscaledDocument;
                }
                if (null !== $this->folder) {
                    $existingDocument->addFolder($this->folder);
                    $this->folder->addDocument($existingDocument);
                }
                $this->logger->info(sprintf(
                    'File %s already exists with same checksum, do not upload it twice.',
                    $existingDocument->getFilename()
                ), [
                    'path' => $existingDocument->getMountPath(),
                ]);
                (new Filesystem())->remove($file->getPathname());

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

        $this->sanitizeSvgFileIfNeeded($file, $document);
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
     * @throws DocumentTypeNotAllowedException
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

        $this->assertFileTypeIsAllowed($this->getFileName());

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

            $document->setFolder(DocumentFolderGenerator::generateFolderName());
        }

        $document->setFilename($this->getFileName());
        if ($file instanceof UploadedFile) {
            $document->setMimeType($file->getClientMimeType());
        } else {
            $document->setMimeType($file->getMimeType() ?? '');
        }
        $this->parseSvgMimeType($document);
        $this->sanitizeSvgFileIfNeeded($file, $document);
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
