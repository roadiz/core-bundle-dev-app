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
    public function __construct(
        private readonly Stopwatch $stopwatch,
        private readonly NodeTypes $nodeTypesBag,
        private readonly NodeSourceApi $nodeSourceApi,
        private readonly RequestStack $requestStack,
        private readonly ManagerRegistry $managerRegistry,
        private readonly CacheItemPoolInterface $cacheAdapter,
        private readonly NodeTypeResolver $nodeTypeResolver,
        private readonly PreviewResolverInterface $previewResolver
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
