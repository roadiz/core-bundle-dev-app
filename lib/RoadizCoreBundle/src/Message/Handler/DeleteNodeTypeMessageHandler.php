<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message\Handler;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeTypeHandler;
use RZ\Roadiz\CoreBundle\Message\DeleteNodeTypeMessage;
use RZ\Roadiz\CoreBundle\Message\UpdateDoctrineSchemaMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class DeleteNodeTypeMessageHandler
{
    public function __construct(
        private NodeTypes $nodeTypesBag,
        private ManagerRegistry $managerRegistry,
        private HandlerFactoryInterface $handlerFactory,
        private MessageBusInterface $messageBus,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(DeleteNodeTypeMessage $message): void
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
        $handler->deleteWithAssociations();

        $this->messageBus->dispatch(
            new Envelope(new UpdateDoctrineSchemaMessage())
        );
        $this->managerRegistry->getManager()->clear();
    }
}
