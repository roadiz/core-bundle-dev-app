<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\EntityThumbnail\Provider;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use RZ\Roadiz\RozierBundle\EntityThumbnail\AbstractEntityThumbnailProvider;
use RZ\Roadiz\RozierBundle\EntityThumbnail\EntityThumbnail;

/**
 * Provides thumbnail for NodesSources entities.
 */
final class NodesSourcesThumbnailProvider extends AbstractEntityThumbnailProvider
{
    public function __construct(
        private readonly DocumentUrlGeneratorInterface $documentUrlGenerator,
        private readonly ManagerRegistry $managerRegistry,
    ) {
    }

    public function supports(string $entityClass, int|string $identifier): bool
    {
        return $this->isClassSupported($entityClass, NodesSources::class);
    }

    public function getThumbnail(string $entityClass, int|string $identifier): ?EntityThumbnail
    {
        if (!$this->isClassSupported($entityClass, NodesSources::class)) {
            return null;
        }

        $repository = $this->managerRegistry->getRepository($entityClass);
        $nodeSource = $repository->find($identifier);

        if (!$nodeSource instanceof NodesSources) {
            return null;
        }

        $title = $nodeSource->getTitle() ?? 'Node';

        // Get the first displayable document associated with this NodesSources
        $documentDto = $this->managerRegistry
            ->getRepository(Document::class)
            ->findOneDisplayableDtoByNodeSource($nodeSource);

        if (null === $documentDto) {
            return $this->createResponse(null, $title, $title);
        }

        if ($documentDto->isPrivate()) {
            return $this->createResponse(null, $title, $title);
        }

        $url = null;
        if ($documentDto->isImage() || $documentDto->isSvg()) {
            $url = $this->documentUrlGenerator
                ->setDocument($documentDto)
                ->setOptions([
                    'width' => 64,
                    'height' => 64,
                    'crop' => '1:1',
                    'quality' => 80,
                    'sharpen' => 3,
                ])
                ->getUrl();
        }

        return $this->createResponse($url, $title, $title);
    }
}
