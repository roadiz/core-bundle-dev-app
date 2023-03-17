<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\TreeWalker\Definition;

use Doctrine\ORM\Tools\Pagination\Paginator;
use RZ\Roadiz\CoreBundle\Api\TreeWalker\NodeSourceWalkerContext;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\TreeWalker\Definition\ContextualDefinitionTrait;
use RZ\TreeWalker\WalkerContextInterface;

final class MultiTypeChildrenDefinition
{
    use ContextualDefinitionTrait;

    private array $types;
    private bool $onlyVisible;

    /**
     * @param WalkerContextInterface $context
     * @param array<string> $types
     * @param bool $onlyVisible
     */
    public function __construct(WalkerContextInterface $context, array $types, bool $onlyVisible = true)
    {
        $this->context = $context;
        $this->types = $types;
        $this->onlyVisible = $onlyVisible;
    }

    /**
     * @param NodesSources $source
     * @return array|Paginator
     */
    public function __invoke(NodesSources $source)
    {
        if ($this->context instanceof NodeSourceWalkerContext) {
            $this->context->getStopwatch()->start(self::class);
            $bag = $this->context->getNodeTypesBag();
            $criteria = [
                'node.parent' => $source->getNode(),
                'translation' => $source->getTranslation(),
                'node.nodeType' => array_map(function (string $singleType) use ($bag) {
                    return $bag->get($singleType);
                }, $this->types)
            ];
            if ($this->onlyVisible) {
                $criteria['node.visible'] = true;
            }
            $children = $this->context->getNodeSourceApi()->getBy($criteria, [
                'node.position' => 'ASC',
            ]);
            $this->context->getStopwatch()->stop(self::class);

            return $children;
        }
        throw new \InvalidArgumentException('Context should be instance of ' . NodeSourceWalkerContext::class);
    }
}
