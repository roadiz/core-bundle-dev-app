<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\TreeWalker;

use Psr\Cache\InvalidArgumentException;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\CoreBundle\Api\TreeWalker\Definition\MultiTypeChildrenDefinition;
use RZ\TreeWalker\AbstractCycleAwareWalker;
use RZ\TreeWalker\Definition\ZeroChildrenDefinition;

/**
 * AutoChildrenNodeSourceWalker automatically creates Walker definitions based on your Node-types
 * children fields default values.
 *
 * Override this class to customize definitions
 */
class AutoChildrenNodeSourceWalker extends AbstractCycleAwareWalker
{
    protected function initializeDefinitions(): void
    {
        if ($this->isRoot()) {
            $context = $this->getContext();
            if ($context instanceof NodeSourceWalkerContext) {
                /** @var NodeTypeInterface $nodeType */
                foreach ($context->getNodeTypesBag()->all() as $nodeType) {
                    $this->addDefinition(
                        $nodeType->getSourceEntityFullQualifiedClassName(),
                        $this->createDefinitionForNodeType($nodeType)
                    );
                }

                $this->initializeAdditionalDefinitions();
            }
        }
    }

    protected function initializeAdditionalDefinitions(): void
    {
        // override this for custom tree-walker definitions
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function createDefinitionForNodeType(NodeTypeInterface $nodeType): callable
    {
        $context = $this->getContext();
        if (!$context instanceof NodeSourceWalkerContext) {
            throw new \InvalidArgumentException('TreeWalker context must be instance of '.NodeSourceWalkerContext::class);
        }
        $childrenNodeTypes = $context->getNodeTypeResolver()->getChildrenNodeTypeList($nodeType);
        if (count($childrenNodeTypes) > 0) {
            return new MultiTypeChildrenDefinition($this->getContext(), $childrenNodeTypes);
        }

        return new ZeroChildrenDefinition($this->getContext());
    }
}
