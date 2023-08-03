<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\TreeWalker;

use Psr\Cache\CacheItemPoolInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\EntityApi\NodeSourceApi;
use RZ\TreeWalker\WalkerContextInterface;
use RZ\TreeWalker\WalkerInterface;
use Symfony\Component\String\UnicodeString;

final class TreeWalkerGenerator
{
    private NodeSourceApi $nodeSourceApi;
    private NodeTypes $nodeTypesBag;
    private WalkerContextInterface $walkerContext;
    private CacheItemPoolInterface $cacheItemPool;

    public function __construct(
        NodeSourceApi $nodeSourceApi,
        NodeTypes $nodeTypesBag,
        WalkerContextInterface $walkerContext,
        CacheItemPoolInterface $cacheItemPool
    ) {
        $this->nodeSourceApi = $nodeSourceApi;
        $this->nodeTypesBag = $nodeTypesBag;
        $this->walkerContext = $walkerContext;
        $this->cacheItemPool = $cacheItemPool;
    }

    /**
     * @param string $nodeType
     * @param class-string $walkerClass
     * @param TranslationInterface $translation
     * @param int $maxLevel
     * @return array<string, WalkerInterface>
     */
    public function getTreeWalkersForTypeAtRoot(
        string $nodeType,
        string $walkerClass,
        TranslationInterface $translation,
        int $maxLevel = 3
    ): array {
        $walkers = [];
        /** @var NodesSources[] $roots */
        $roots = $this->nodeSourceApi->getBy([
            'node.nodeType' => $this->nodeTypesBag->get($nodeType),
            'node.parent' => null,
            'translation' => $translation,
        ]);

        foreach ($roots as $root) {
            $walkerName = (new UnicodeString($root->getNode()->getNodeName() . ' walker'))
                ->trim()
                ->camel()
                ->toString();

            /** @var callable $callable */
            $callable = [$walkerClass, 'build'];
            $walkers[$walkerName] = call_user_func(
                $callable,
                $root,
                $this->walkerContext,
                $maxLevel,
                $this->cacheItemPool
            );
        }

        return $walkers;
    }
}
