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

    /**
     * @param WalkerContextInterface $context
     * @param array<string> $types
     * @param bool $onlyVisible
     */
    public function __construct(
        private readonly WalkerContextInterface $context,
        private readonly array $types,
        private readonly bool $onlyVisible = true
    ) {
    }

    /**
     * @param NodesSources $source
     * @return array|Paginator
     */
    public function __invoke(NodesSources $source)
    {
        if (!($this->context instanceof NodeSourceWalkerContext)) {
            throw new \InvalidArgumentException('Context should be instance of ' . NodeSourceWalkerContext::class);
        }

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
}
