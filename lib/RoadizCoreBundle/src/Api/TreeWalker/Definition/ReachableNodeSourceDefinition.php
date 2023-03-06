<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\TreeWalker\Definition;

use ArrayIterator;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
use RZ\Roadiz\CoreBundle\Api\TreeWalker\NodeSourceWalkerContext;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\TreeWalker\Definition\ContextualDefinitionTrait;

final class ReachableNodeSourceDefinition
{
    use ContextualDefinitionTrait;

    /**
     * @throws Exception
     */
    public function __invoke(NodesSources $source): array
    {
        if ($this->context instanceof NodeSourceWalkerContext) {
            $this->context->getStopwatch()->start(self::class);
            $children = $this->context->getNodeSourceApi()->getBy([
                'node.parent' => $source->getNode(),
                'node.visible' => true,
                'translation' => $source->getTranslation(),
                'node.nodeType.reachable' => true
            ], [
                'node.position' => 'ASC',
            ]);
            $this->context->getStopwatch()->stop(self::class);

            if ($children instanceof Paginator) {
                $iterator = $children->getIterator();
                if ($iterator instanceof ArrayIterator) {
                    return $iterator->getArrayCopy();
                }
            }
            return $children;
        }
        throw new \InvalidArgumentException('Context should be instance of ' . NodeSourceWalkerContext::class);
    }
}
