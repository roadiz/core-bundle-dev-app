<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message\Handler;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Realm;
use RZ\Roadiz\CoreBundle\Entity\RealmNode;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeHandler;
use RZ\Roadiz\CoreBundle\Message\CleanRealmNodeInheritanceMessage;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class CleanRealmNodeInheritanceMessageHandler implements MessageHandlerInterface
{
    private ManagerRegistry $managerRegistry;
    private HandlerFactoryInterface $handlerFactory;

    public function __construct(ManagerRegistry $managerRegistry, HandlerFactoryInterface $handlerFactory)
    {
        $this->managerRegistry = $managerRegistry;
        $this->handlerFactory = $handlerFactory;
    }

    public function __invoke(CleanRealmNodeInheritanceMessage $message): void
    {
        if ($message->getRealmId() === null) {
            return;
        }
        $node = $this->managerRegistry->getRepository(Node::class)->find($message->getNodeId());
        $realm = $this->managerRegistry->getRepository(Realm::class)->find($message->getRealmId());

        if (null === $node) {
            throw new UnrecoverableMessageHandlingException('Node does not exist');
        }
        if (null === $realm) {
            throw new UnrecoverableMessageHandlingException('Realm does not exist');
        }

        /** @var NodeHandler $nodeHandler */
        $nodeHandler = $this->handlerFactory->getHandler($node);
        $childrenIds = $nodeHandler->getAllOffspringId();

        $realmNodes = $this->managerRegistry
            ->getRepository(RealmNode::class)
            ->findByNodeIdsAndRealmId(
                $childrenIds,
                $message->getRealmId()
            );

        foreach ($realmNodes as $realmNode) {
            $this->managerRegistry->getManager()->remove($realmNode);
        }

        $this->managerRegistry->getManager()->flush();
    }
}
