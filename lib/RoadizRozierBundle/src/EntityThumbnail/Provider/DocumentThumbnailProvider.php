<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\EntityThumbnail\Provider;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use RZ\Roadiz\RozierBundle\EntityThumbnail\AbstractEntityThumbnailProvider;

/**
 * Provides thumbnail for Document entities.
 */
final class DocumentThumbnailProvider extends AbstractEntityThumbnailProvider
{
    public function __construct(
        private readonly DocumentUrlGeneratorInterface $documentUrlGenerator,
        private readonly ManagerRegistry $managerRegistry,
    ) {
    }

    public function supports(string $entityClass, int|string $identifier): bool
    {
        return $this->isClassSupported($entityClass, Document::class);
    }

    public function getThumbnail(string $entityClass, int|string $identifier): ?array
    {
        if (!$this->isClassSupported($entityClass, Document::class)) {
            return null;
        }

        $repository = $this->managerRegistry->getRepository($entityClass);
        $document = $repository->find($identifier);

        if (!$document instanceof Document) {
            return null;
        }

        // Don't show thumbnail for private documents
        if ($document->isPrivate()) {
            return $this->createResponse(null, null, $document->getFilename());
        }

        $url = null;
        if ($document->isImage() || $document->isSvg() || $document->isPdf()) {
            $url = $this->documentUrlGenerator
                ->setDocument($document)
                ->setOptions([
                    'width' => 64,
                    'height' => 64,
                    'crop' => '1:1',
                    'quality' => 80,
                    'sharpen' => 3,
                ])
                ->getUrl();
        }

        return $this->createResponse(
            $url,
            $document->getFilename() ?? 'Document',
            $document->getFilename()
        );
    }
}
