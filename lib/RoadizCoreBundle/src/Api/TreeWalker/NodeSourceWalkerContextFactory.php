<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\TreeWalker;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheItemPoolInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\EntityApi\NodeSourceApi;
use RZ\Roadiz\CoreBundle\NodeType\NodeTypeResolver;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\TreeWalker\WalkerContextInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Stopwatch\Stopwatch;

final class NodeSourceWalkerContextFactory implements WalkerContextFactoryInterface
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

    public function createWalkerContext(): WalkerContextInterface
    {
        return new NodeSourceWalkerContext(
            $this->stopwatch,
            $this->nodeTypesBag,
            $this->nodeSourceApi,
            $this->requestStack,
            $this->managerRegistry,
            $this->cacheAdapter,
            $this->nodeTypeResolver,
            $this->previewResolver
        );
    }
}
