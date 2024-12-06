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

final readonly class NodeSourceWalkerContextFactory implements WalkerContextFactoryInterface
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
