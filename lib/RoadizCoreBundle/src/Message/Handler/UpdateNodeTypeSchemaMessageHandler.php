<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message\Handler;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeTypeHandler;
use RZ\Roadiz\CoreBundle\Message\UpdateDoctrineSchemaMessage;
use RZ\Roadiz\CoreBundle\Message\UpdateNodeTypeSchemaMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

/** @deprecated nodeTypes will be static in future Roadiz versions */
#[AsMessageHandler]
final readonly class UpdateNodeTypeSchemaMessageHandler
{
    public function __construct(
        private NodeTypes $nodeTypesBag,
        private ManagerRegistry $managerRegistry,
        private HandlerFactoryInterface $handlerFactory,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(UpdateNodeTypeSchemaMessage $message): void
    {
        $nodeTypeId = $message->getNodeTypeId();

        if (null === $nodeTypeId) {
            throw new UnrecoverableMessageHandlingException('NodeTypeId is required');
        }

        if (is_string($nodeTypeId)) {
            $nodeType = $this->nodeTypesBag->get($nodeTypeId);
        } else {
            $nodeType = $this->nodeTypesBag->getById($nodeTypeId);
        }

        if (!$nodeType instanceof NodeType) {
            throw new UnrecoverableMessageHandlingException('NodeType does not exist');
        }

        /** @var NodeTypeHandler $handler */
        $handler = $this->handlerFactory->getHandler($nodeType);
        $handler->updateSchema();

        $this->managerRegistry->getManager()->clear();
        $this->messageBus->dispatch(
            new Envelope(new UpdateDoctrineSchemaMessage())
        );
    }
}
