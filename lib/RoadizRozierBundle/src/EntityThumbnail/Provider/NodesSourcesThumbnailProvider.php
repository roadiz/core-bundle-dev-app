<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\EntityThumbnail\Provider;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Repository\DocumentRepository;
use RZ\Roadiz\CoreBundle\Repository\NotPublishedNodesSourcesRepository;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use RZ\Roadiz\RozierBundle\EntityThumbnail\AbstractEntityThumbnailProvider;
use RZ\Roadiz\RozierBundle\EntityThumbnail\EntityThumbnail;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Provides thumbnail for NodesSources entities.
 */
final readonly class NodesSourcesThumbnailProvider extends AbstractEntityThumbnailProvider
{
    public function __construct(
        private NotPublishedNodesSourcesRepository $nodesSourcesRepository,
        private DocumentRepository $documentRepository,
        private DocumentThumbnailProvider $documentThumbnailProvider,
        private Security $security,
    ) {
    }

    #[\Override]
    public function supports(string $entityClass, int|string $identifier): bool
    {
        return $this->isClassSupported($entityClass, NodesSources::class);
    }

    #[\Override]
    public function getThumbnail(string $entityClass, int|string $identifier): ?EntityThumbnail
    {
        if (!$this->isClassSupported($entityClass, NodesSources::class)) {
            return null;
        }

        $nodeSource = $this->nodesSourcesRepository->find($identifier);

        if (!$nodeSource instanceof NodesSources) {
            return null;
        }

        if (false === $this->security->isGranted(NodeVoter::READ, $nodeSource)) {
            return null;
        }

        $title = $nodeSource->getTitle() ?? 'Node';

        // Get the first displayable document associated with this NodesSources
        $documentDto = $this->documentRepository->findOneDisplayableDtoByNodeSource($nodeSource);

        if (
            null === $documentDto
            || $documentDto->isPrivate()
            || !($documentDto->isImage() || $documentDto->isSvg())
        ) {
            return null;
        }

        return new EntityThumbnail(
            url: $this->documentThumbnailProvider->getDocumentUrl($documentDto),
            alt: $title,
            title: $title,
            width: 64,
            height: 64,
        );
    }
}
