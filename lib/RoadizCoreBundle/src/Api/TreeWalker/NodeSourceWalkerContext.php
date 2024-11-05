<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\TreeWalker;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Psr\Cache\CacheItemPoolInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\EntityApi\NodeSourceApi;
use RZ\Roadiz\CoreBundle\NodeType\NodeTypeResolver;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\TreeWalker\WalkerContextInterface;
use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Stopwatch\Stopwatch;

#[Exclude]
class NodeSourceWalkerContext implements WalkerContextInterface
{
    public function __construct(
        private readonly Stopwatch $stopwatch,
        private readonly NodeTypes $nodeTypesBag,
        private readonly NodeSourceApi $nodeSourceApi,
        private readonly RequestStack $requestStack,
        private readonly ManagerRegistry $managerRegistry,
        private readonly CacheItemPoolInterface $cacheAdapter,
        private readonly NodeTypeResolver $nodeTypeResolver,
        private readonly PreviewResolverInterface $previewResolver,
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
     * @deprecated Use getManagerRegistry
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

    public function getManagerRegistry(): ManagerRegistry
    {
        return $this->managerRegistry;
    }

    public function getEntityManager(): ObjectManager
    {
        return $this->getManagerRegistry()->getManager();
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
