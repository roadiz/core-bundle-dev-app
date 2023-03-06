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
     * @var array
     */
    private $types;

    /**
     * @param WalkerContextInterface $context
     * @param array<string> $types
     */
    public function __construct(WalkerContextInterface $context, array $types)
    {
        $this->context = $context;
        $this->types = $types;
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
            $children = $this->context->getNodeSourceApi()->getBy([
                'node.parent' => $source->getNode(),
                'node.visible' => true,
                'translation' => $source->getTranslation(),
                'node.nodeType' => array_map(function (string $singleType) use ($bag) {
                    return $bag->get($singleType);
                }, $this->types)
            ], [
                'node.position' => 'ASC',
            ]);
            $this->context->getStopwatch()->stop(self::class);

            return $children;
        }
        throw new \InvalidArgumentException('Context should be instance of ' . NodeSourceWalkerContext::class);
    }
}
