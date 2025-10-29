<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\EntityThumbnail\Provider;

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
    ) {
    }

    public function supports(object $entity): bool
    {
        return $entity instanceof Document;
    }

    public function getThumbnail(object $entity): array
    {
        if (!$entity instanceof Document) {
            return $this->createResponse(null);
        }

        // Don't show thumbnail for private documents
        if ($entity->isPrivate()) {
            return $this->createResponse(null, null, $entity->getFilename());
        }

        $url = null;
        if ($entity->isImage() || $entity->isSvg() || $entity->isPdf()) {
            $url = $this->documentUrlGenerator
                ->setDocument($entity)
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
            $entity->getFilename() ?? 'Document',
            $entity->getFilename()
        );
    }
}
