<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheItemPoolInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;

final class OptimizedNodesSourcesGraphPathAggregator implements NodesSourcesPathAggregator
{
    private ManagerRegistry $managerRegistry;
    private CacheItemPoolInterface $cacheAdapter;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param CacheItemPoolInterface $cacheAdapter
     */
    public function __construct(ManagerRegistry $managerRegistry, CacheItemPoolInterface $cacheAdapter)
    {
        $this->managerRegistry = $managerRegistry;
        $this->cacheAdapter = $cacheAdapter;
    }

    private function getCacheKey(NodesSources $nodesSources): string
    {
        return 'ns_url_' . $nodesSources->getId();
    }

    /**
     * @param NodesSources $nodesSources
     * @param array $parameters
     * @return string
     */
    public function aggregatePath(NodesSources $nodesSources, array $parameters = []): string
    {
        if (
            isset($parameters[NodeRouter::NO_CACHE_PARAMETER]) &&
            $parameters[NodeRouter::NO_CACHE_PARAMETER] === true
        ) {
            $urlTokens = array_reverse($this->getIdentifiers($nodesSources));
            return implode('/', $urlTokens);
        }

        $cacheItem = $this->cacheAdapter->getItem($this->getCacheKey($nodesSources));
        if (!$cacheItem->isHit()) {
            $urlTokens = array_reverse($this->getIdentifiers($nodesSources));
            $cacheItem->set(implode('/', $urlTokens));
            $this->cacheAdapter->save($cacheItem);
        }
        return $cacheItem->get();
    }

    /**
     * @param Node $parent
     *
     * @return array
     */
    protected function getParentsIds(Node $parent): array
    {
        $parentIds = [];
        while ($parent !== null && !$parent->isHome()) {
            $parentIds[] = $parent->getId();
            $parent = $parent->getParent();
        }

        return $parentIds;
    }

    /**
     * Get every nodeSource parents identifier from current to
     * farest ancestor.
     *
     * @param NodesSources $source
     *
     * @return array
     */
    protected function getIdentifiers(NodesSources $source): array
    {
        $urlTokens = [];
        $parents = [];
        /** @var Node|null $parentNode */
        $parentNode = $source->getNode()->getParent();

        if (null !== $parentNode) {
            $parentIds = $this->getParentsIds($parentNode);
            if (count($parentIds) > 0) {
                /**
                 *
                 * Do a partial query to optimize SQL time
                 */
                $qb = $this->managerRegistry
                    ->getRepository(NodesSources::class)
                    ->createQueryBuilder('ns');
                $parents = $qb->select('n.id as id, n.nodeName as nodeName, ua.alias as alias')
                    ->innerJoin('ns.node', 'n')
                    ->leftJoin('ns.urlAliases', 'ua')
                    ->andWhere($qb->expr()->in('n.id', ':parentIds'))
                    ->andWhere($qb->expr()->eq('n.visible', ':visible'))
                    ->andWhere($qb->expr()->eq('ns.translation', ':translation'))
                    ->setParameters([
                        'parentIds' => $parentIds,
                        'visible' => true,
                        'translation' => $source->getTranslation()
                    ])
                    ->getQuery()
                    ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
                    ->setCacheable(true)
                    ->getArrayResult()
                ;
                usort($parents, function ($a, $b) use ($parentIds) {
                    return array_search($a['id'], $parentIds) -
                        array_search($b['id'], $parentIds);
                });
            }
        }

        $urlTokens[] = $source->getIdentifier();

        foreach ($parents as $parent) {
            $urlTokens[] = $parent['alias'] ?? $parent['nodeName'];
        }

        return $urlTokens;
    }
}