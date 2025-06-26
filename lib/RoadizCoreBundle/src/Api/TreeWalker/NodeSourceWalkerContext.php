<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\TreeWalker;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Psr\Cache\CacheItemPoolInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\EntityApi\NodeSourceApi;
use RZ\Roadiz\CoreBundle\NodeType\NodeTypeResolver;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\Repository\StatusAwareRepository;
use RZ\TreeWalker\WalkerContextInterface;
use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Stopwatch\Stopwatch;

#[Exclude]
readonly class NodeSourceWalkerContext implements WalkerContextInterface
{
    public function __construct(
        private Stopwatch $stopwatch,
        private NodeTypes $nodeTypesBag,
        private NodeSourceApi $nodeSourceApi,
        private RequestStack $requestStack,
        private ManagerRegistry $managerRegistry,
        private CacheItemPoolInterface $cacheAdapter,
        private NodeTypeResolver $nodeTypeResolver,
        private PreviewResolverInterface $previewResolver,
    ) {
    }

    public function getStopwatch(): Stopwatch
    {
        return $this->stopwatch;
    }

    public function getNodeTypesBag(): NodeTypes
    {
        return $this->nodeTypesBag;
    }

    /**
     * @deprecated Use getRepository
     */
    public function getNodeSourceApi(): NodeSourceApi
    {
        return $this->nodeSourceApi;
    }

    public function getRequestStack(): RequestStack
    {
        return $this->requestStack;
    }

    /**
     * @deprecated Use getMainRequest
     */
    public function getMasterRequest(): ?Request
    {
        return $this->requestStack->getMainRequest();
    }

    public function getMainRequest(): ?Request
    {
        return $this->requestStack->getMainRequest();
    }

    /**
     * @deprecated Use getRepository to ensure correct repository state
     */
    public function getManagerRegistry(): ManagerRegistry
    {
        return $this->managerRegistry;
    }

    /**
     * @param class-string $className
     */
    public function getRepository(string $className): ObjectRepository
    {
        $repository = $this->managerRegistry->getRepository($className);

        /*
         * We need to reset repository status state, because StatusAwareRepository is not a stateless service.
         * When using worker PHP runtimes (such as FrankenPHP or Swoole), this can lead to unpublish nodes being returned.
         */
        if ($repository instanceof StatusAwareRepository) {
            $repository->setDisplayingNotPublishedNodes(false);
            $repository->setDisplayingAllNodesStatuses(false);
        }

        return $repository;
    }

    public function getEntityManager(): ObjectManager
    {
        return $this->managerRegistry->getManager();
    }

    public function getCacheAdapter(): CacheItemPoolInterface
    {
        return $this->cacheAdapter;
    }

    public function getNodeTypeResolver(): NodeTypeResolver
    {
        return $this->nodeTypeResolver;
    }

    public function getPreviewResolver(): PreviewResolverInterface
    {
        return $this->previewResolver;
    }
}
