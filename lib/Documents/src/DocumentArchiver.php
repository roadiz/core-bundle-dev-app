<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Easily create and serve ZIP archives from your Roadiz documents.
 */
final class DocumentArchiver
{
    private FilesystemOperator $documentsStorage;

    public function __construct(FilesystemOperator $documentsStorage)
    {
        $this->documentsStorage = $documentsStorage;
    }

    /**
     * @param iterable<DocumentInterface> $documents
     * @param string $name
     * @param bool $keepFolders
     * @return string Zip file path
     * @throws FilesystemException
     */
    public function archive(iterable $documents, string $name, bool $keepFolders = true): string
    {
        $filename = (new AsciiSlugger())->slug($name . ' ' . date('YmdHis'), '_') . '.zip';
        $tmpFileName = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;

        $zip = new \ZipArchive();
        $zip->open($tmpFileName, \ZipArchive::CREATE);

        /** @var DocumentInterface $document */
        foreach ($documents as $document) {
            if (null !== $rawDocument = $document->getRawDocument()) {
                $document = $rawDocument;
            }
            if ($document->isLocal()) {
                $mountPath = $document->getMountPath();
                if (null !== $mountPath && $this->documentsStorage->fileExists($mountPath)) {
                    if ($keepFolders) {
                        $zipPathname = $document->getFolder() . DIRECTORY_SEPARATOR . $document->getFilename();
                    } else {
                        $zipPathname = $document->getFilename();
                    }
                    $zip->addFromString($zipPathname, $this->documentsStorage->read($mountPath));
                }
            }
        }
        $zip->close();

        return $tmpFileName;
    }

    /**
     * @param iterable<DocumentInterface> $documents
     * @param string $name
     * @param bool $keepFolders
     * @param bool $unlink
     * @return BinaryFileResponse
     * @throws FilesystemException
     */
    public function archiveAndServe(
        iterable $documents,
        string $name,
        bool $keepFolders = true,
        bool $unlink = true
    ): BinaryFileResponse {
        $filename = $this->archive($documents, $name, $keepFolders);
        $response = new BinaryFileResponse(
            $filename,
            Response::HTTP_OK,
            [],
            false,
            'attachment'
        );
        $response->deleteFileAfterSend($unlink);
        return $response;
    }
}
