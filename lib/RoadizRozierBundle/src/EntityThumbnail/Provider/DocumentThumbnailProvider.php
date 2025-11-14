<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\EntityThumbnail\Provider;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Documents\Models\BaseDocumentInterface;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use RZ\Roadiz\RozierBundle\EntityThumbnail\AbstractEntityThumbnailProvider;
use RZ\Roadiz\RozierBundle\EntityThumbnail\EntityThumbnail;

/**
 * Provides thumbnail for Document entities.
 */
final readonly class DocumentThumbnailProvider extends AbstractEntityThumbnailProvider
{
    public function __construct(
        private DocumentUrlGeneratorInterface $documentUrlGenerator,
        private ManagerRegistry $managerRegistry,
    ) {
    }

    #[\Override]
    public function supports(string $entityClass, int|string $identifier): bool
    {
        return $this->isClassSupported($entityClass, Document::class);
    }

    public function getDocumentUrl(BaseDocumentInterface $document, int $size = 64): ?string
    {
        $url = $this->documentUrlGenerator
            ->setDocument($document)
            ->setOptions([
                'width' => $size,
                'height' => $size,
                'crop' => '1:1',
                'quality' => 80,
                'sharpen' => 3,
            ])
            ->getUrl()
        ;

        if ($document->isProcessable() && !str_ends_with($url, '.webp')) {
            $url .= '.webp';
        }

        return $url;
    }

    #[\Override]
    public function getThumbnail(string $entityClass, int|string $identifier): ?EntityThumbnail
    {
        if (!$this->isClassSupported($entityClass, Document::class)) {
            return null;
        }

        $repository = $this->managerRegistry->getRepository($entityClass);
        $document = $repository->find($identifier);

        if (!$document instanceof BaseDocumentInterface) {
            return null;
        }

        // Don't show thumbnail for private documents or non-image documents
        if ($document->isPrivate() || !($document->isImage() || $document->isSvg())) {
            return new EntityThumbnail(
                title: $document->getFilename()
            );
        }

        $size = 64;

        return new EntityThumbnail(
            url: $this->getDocumentUrl($document, $size),
            alt: $document->getAlternativeText() ?? '',
            title: $document->getFilename(),
            width: $size,
            height: $size,
        );
    }
}
