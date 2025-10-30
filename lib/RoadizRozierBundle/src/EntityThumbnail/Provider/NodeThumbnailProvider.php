<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\EntityThumbnail\Provider;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\NodeInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\RozierBundle\EntityThumbnail\AbstractEntityThumbnailProvider;
use RZ\Roadiz\RozierBundle\EntityThumbnail\EntityThumbnail;

/**
 * Provides thumbnail for NodesSources entities.
 */
final readonly class NodeThumbnailProvider extends AbstractEntityThumbnailProvider
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private NodesSourcesThumbnailProvider $nodesSourcesThumbnailProvider,
    ) {
    }

    #[\Override]
    public function supports(string $entityClass, int|string $identifier): bool
    {
        return $this->isClassSupported($entityClass, NodeInterface::class);
    }

    #[\Override]
    public function getThumbnail(string $entityClass, int|string $identifier): ?EntityThumbnail
    {
        if (!$this->isClassSupported($entityClass, NodeInterface::class)) {
            return null;
        }

        $repository = $this->managerRegistry->getRepository($entityClass);
        $node = $repository->find($identifier);

        if (!$node instanceof Node) {
            return null;
        }

        $nodesSources = $node->getNodeSources()->findFirst(
            fn (int $index, NodesSources $ns) => $ns->getTranslation()->isDefaultTranslation()
        );
        if (null === $nodesSources) {
            return null;
        }

        return $this->nodesSourcesThumbnailProvider->getThumbnail(
            NodesSources::class,
            $nodesSources->getId(),
        );
    }
}
