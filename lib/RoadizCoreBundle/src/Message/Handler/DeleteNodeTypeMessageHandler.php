<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message\Handler;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeTypeHandler;
use RZ\Roadiz\CoreBundle\Message\DeleteNodeTypeMessage;
use RZ\Roadiz\CoreBundle\Message\UpdateDoctrineSchemaMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class DeleteNodeTypeMessageHandler
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly HandlerFactoryInterface $handlerFactory,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(DeleteNodeTypeMessage $message): void
    {
        $nodeType = $this->managerRegistry->getRepository(NodeType::class)->find($message->getNodeTypeId());

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
