<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\NodeType;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;

final readonly class NodeTypeResolver
{
    public function __construct(private CacheItemPoolInterface $cacheAdapter)
    {
    }

    /**
     * @return array<string>
     */
    protected function getNodeTypeList(NodeTypeFieldInterface $field): array
    {
        $nodeTypesNames = array_map('trim', explode(',', $field->getDefaultValues() ?? ''));

        return array_filter($nodeTypesNames);
    }

    /**
     * @return array<string>
     *
     * @throws InvalidArgumentException
     */
    public function getChildrenNodeTypeList(NodeTypeInterface $nodeType): array
    {
        $cacheKey = 'children_'.$nodeType->getName();

        $cacheItem = $this->cacheAdapter->getItem($cacheKey);
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $childrenTypes = [];
        $childrenFields = $nodeType->getFields()->filter(function (NodeTypeFieldInterface $field) {
            return $field->isChildrenNodes() && null !== $field->getDefaultValues();
        });
        if ($childrenFields->count() > 0) {
            /** @var NodeTypeFieldInterface $field */
            foreach ($childrenFields as $field) {
                $childrenTypes = array_merge($childrenTypes, $this->getNodeTypeList($field));
            }
            $childrenTypes = array_filter(array_unique($childrenTypes));
        }

        $cacheItem = $this->cacheAdapter->getItem($cacheKey);
        $cacheItem->set($childrenTypes);
        $this->cacheAdapter->save($cacheItem);

        return $childrenTypes;
    }
}
