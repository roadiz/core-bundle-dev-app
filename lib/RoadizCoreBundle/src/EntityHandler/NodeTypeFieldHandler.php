<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityHandler;

use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\Core\Handlers\AbstractHandler;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;

/**
 * Handle operations with node-type fields entities.
 */
final class NodeTypeFieldHandler extends AbstractHandler
{
    private ?NodeTypeField $nodeTypeField = null;

    public function getNodeTypeField(): NodeTypeField
    {
        if (null === $this->nodeTypeField) {
            throw new \BadMethodCallException('NodeTypeField is null');
        }

        return $this->nodeTypeField;
    }

    /**
     * @return $this
     */
    public function setNodeTypeField(NodeTypeField $nodeTypeField): self
    {
        $this->nodeTypeField = $nodeTypeField;

        return $this;
    }

    public function __construct(ObjectManager $objectManager, private readonly HandlerFactory $handlerFactory)
    {
        parent::__construct($objectManager);
    }

    /**
     * Clean position for current node siblings.
     *
     * @return float Return the next position after the **last** node
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @deprecated Node-type fields do not have positions anymore
     */
    public function cleanPositions(bool $setPositions = false): float
    {
        if ($this->nodeTypeField->getNodeType() instanceof NodeType) {
            /** @var NodeTypeHandler $nodeTypeHandler */
            $nodeTypeHandler = $this->handlerFactory->getHandler($this->nodeTypeField->getNodeType());

            return $nodeTypeHandler->cleanPositions();
        }

        return 1;
    }
}
