<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityHandler;

use Doctrine\Persistence\ObjectManager;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use RZ\Roadiz\Core\Handlers\AbstractHandler;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Repository\FolderRepository;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Handle operations with documents entities.
 */
final class DocumentHandler extends AbstractHandler
{
    private ?DocumentInterface $document = null;

    public function __construct(ObjectManager $objectManager, private readonly FilesystemOperator $documentStorage)
    {
        parent::__construct($objectManager);
    }

    /**
     * Get a Response object to force download document.
     * This method works for both private and public documents.
     *
     * @param bool $asAttachment
     * @return StreamedResponse
     * @throws FilesystemException
     */
    public function getDownloadResponse(bool $asAttachment = true): StreamedResponse
    {
        if ($this->document->isLocal()) {
            $documentPath = $this->document->getMountPath();

            if ($this->documentStorage->fileExists($documentPath)) {
                $headers = [
                    "Content-Type" => $this->documentStorage->mimeType($documentPath),
                    "Content-Length" => $this->documentStorage->fileSize($documentPath),
                ];
                if ($asAttachment) {
                    $headers["Content-disposition"] = "attachment; filename=\"" . basename($this->document->getFilename()) . "\"";
                }
                return new StreamedResponse(function () use ($documentPath) {
                    \fpassthru($this->documentStorage->readStream($documentPath));
                }, Response::HTTP_OK, $headers);
            }
        }

        throw new ResourceNotFoundException();
    }

    /**
     * Return documents folders with the same translation as
     * current document.
     *
     * @param Translation|null $translation
     * @return array
     */
    public function getFolders(Translation $translation = null): array
    {
        if (!$this->document instanceof Document) {
            return [];
        }
        /** @var FolderRepository $repository */
        $repository = $this->objectManager->getRepository(Folder::class);
        if (null !== $translation) {
            return $repository->findByDocumentAndTranslation($this->document, $translation);
        }

        $docTranslation = $this->document->getDocumentTranslations()->first();
        if ($docTranslation instanceof DocumentTranslation) {
            return $repository->findByDocumentAndTranslation($this->document, $docTranslation->getTranslation());
        }

        return $repository->findByDocumentAndTranslation($this->document);
    }

    public function getDocument(): ?DocumentInterface
    {
        return $this->document;
    }

    /**
     * @param DocumentInterface $document
     * @return $this
     */
    public function setDocument(DocumentInterface $document): self
    {
        $this->document = $document;
        return $this;
    }
}
