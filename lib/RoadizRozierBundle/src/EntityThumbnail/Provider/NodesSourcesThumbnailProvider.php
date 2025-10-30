<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\EntityThumbnail\Provider;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use RZ\Roadiz\RozierBundle\EntityThumbnail\AbstractEntityThumbnailProvider;

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

        // Get the first image document associated with this NodesSources
        $nodesSourcesDocument = $this->managerRegistry
            ->getRepository(NodesSourcesDocuments::class)
            ->findOneBy([
                'nodeSource' => $nodeSource,
            ], [
                'position' => 'ASC',
            ]);

        if (null === $nodesSourcesDocument) {
            return $this->createResponse(null, $title, $title);
        }

        $document = $nodesSourcesDocument->getDocument();
        if (null === $document || $document->isPrivate()) {
            return $this->createResponse(null, $title, $title);
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

        return $this->createResponse($url, $title, $title);
    }
}
