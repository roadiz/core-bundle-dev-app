<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\EntityThumbnail\Provider;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
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
        private ManagerRegistry $managerRegistry,
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

        $repository = $this->managerRegistry->getRepository($entityClass);
        $nodeSource = $repository->find($identifier);

        if (!$nodeSource instanceof NodesSources) {
            return null;
        }

        if (false === $this->security->isGranted(NodeVoter::READ, $nodeSource)) {
            return null;
        }

        $title = $nodeSource->getTitle() ?? 'Node';

        // Get the first displayable document associated with this NodesSources
        $documentDto = $this->managerRegistry
            ->getRepository(Document::class)
            ->findOneDisplayableDtoByNodeSource($nodeSource);

        if (null === $documentDto || $documentDto->isPrivate()) {
            return new EntityThumbnail(
                title: $title,
            );
        }

        $url = null;
        if ($documentDto->isImage() || $documentDto->isSvg()) {
            $url = $this->documentThumbnailProvider->getDocumentUrl($documentDto);
        }

        return new EntityThumbnail(
            url: $url,
            alt: $title,
            title: $title,
            width: 64,
            height: 64,
        );
    }
}
