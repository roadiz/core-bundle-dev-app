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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Stopwatch\Stopwatch;

class NodeSourceWalkerContext implements WalkerContextInterface
{
    private Stopwatch $stopwatch;
    private NodeTypes $nodeTypesBag;
    private NodeSourceApi $nodeSourceApi;
    private RequestStack $requestStack;
    private ManagerRegistry $managerRegistry;
    private CacheItemPoolInterface $cacheAdapter;
    private NodeTypeResolver $nodeTypeResolver;
    private PreviewResolverInterface $previewResolver;

    public function __construct(
        Stopwatch $stopwatch,
        NodeTypes $nodeTypesBag,
        NodeSourceApi $nodeSourceApi,
        RequestStack $requestStack,
        ManagerRegistry $managerRegistry,
        CacheItemPoolInterface $cacheAdapter,
        NodeTypeResolver $nodeTypeResolver,
        PreviewResolverInterface $previewResolver
    ) {
        $this->stopwatch = $stopwatch;
        $this->nodeTypesBag = $nodeTypesBag;
        $this->nodeSourceApi = $nodeSourceApi;
        $this->requestStack = $requestStack;
        $this->managerRegistry = $managerRegistry;
        $this->cacheAdapter = $cacheAdapter;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->previewResolver = $previewResolver;
    }

    /**
     * @return Stopwatch
     */
    public function getStopwatch(): Stopwatch
    {
        return $this->stopwatch;
    }

    /**
     * @return NodeTypes
     */
    public function getNodeTypesBag(): NodeTypes
    {
        return $this->nodeTypesBag;
    }

    /**
     * @return NodeSourceApi
     */
    public function getNodeSourceApi(): NodeSourceApi
    {
        return $this->nodeSourceApi;
    }

    /**
     * @return RequestStack
     */
    public function getRequestStack(): RequestStack
    {
        return $this->requestStack;
    }

    /**
     * @return Request|null
     * @deprecated Use getMainRequest
     */
    public function getMasterRequest(): ?Request
    {
        return $this->requestStack->getMainRequest();
    }

    /**
     * @return Request|null
     */
    public function getMainRequest(): ?Request
    {
        return $this->requestStack->getMainRequest();
    }

    /**
     * @return ManagerRegistry
     */
    public function getManagerRegistry(): ManagerRegistry
    {
        return $this->managerRegistry;
    }

    /**
     * @return ObjectManager
     */
    public function getEntityManager(): ObjectManager
    {
        return $this->getManagerRegistry()->getManager();
    }

    /**
     * @return CacheItemPoolInterface
     */
    public function getCacheAdapter(): CacheItemPoolInterface
    {
        return $this->cacheAdapter;
    }

    /**
     * @return NodeTypeResolver
     */
    public function getNodeTypeResolver(): NodeTypeResolver
    {
        return $this->nodeTypeResolver;
    }

    /**
     * @return PreviewResolverInterface
     */
    public function getPreviewResolver(): PreviewResolverInterface
    {
        return $this->previewResolver;
    }
}
