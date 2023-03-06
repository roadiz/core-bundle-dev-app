<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message\Handler;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\RealmNode;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeHandler;
use RZ\Roadiz\CoreBundle\Message\ApplyRealmNodeInheritanceMessage;
use RZ\Roadiz\CoreBundle\Message\CleanRealmNodeInheritanceMessage;
use RZ\Roadiz\CoreBundle\Message\SearchRealmNodeInheritanceMessage;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class SearchRealmNodeInheritanceMessageHandler implements MessageHandlerInterface
{
    private ManagerRegistry $managerRegistry;
    private HandlerFactoryInterface $handlerFactory;
    private MessageBusInterface $bus;
    private LoggerInterface $logger;

    public function __construct(
        ManagerRegistry $managerRegistry,
        HandlerFactoryInterface $handlerFactory,
        MessageBusInterface $bus,
        LoggerInterface $logger
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->handlerFactory = $handlerFactory;
        $this->bus = $bus;
        $this->logger = $logger;
    }

    public function __invoke(SearchRealmNodeInheritanceMessage $message): void
    {
        /** @var Node|null $node */
        $node = $this->managerRegistry->getRepository(Node::class)->find($message->getNodeId());
        if (null === $node) {
            throw new UnrecoverableMessageHandlingException('Node does not exist');
        }

        $this->clearAnyExistingRealmNodes($node);
        $this->applyRootRealmNodes($node);
    }

    private function clearAnyExistingRealmNodes(Node $node): void
    {
        /** @var RealmNode[] $autoRealmNodes */
        $autoRealmNodes = $this->managerRegistry->getRepository(RealmNode::class)->findBy([
            'node' => $node,
            'inheritanceType' => RealmInterface::INHERITANCE_AUTO
        ]);

        /*
         * If there are existing auto realmNode from former ancestor, we need to clean them
         */
        foreach ($autoRealmNodes as $autoRealmNode) {
            $this->logger->info('Clean existing RealmNode information');
            $this->bus->dispatch(new Envelope(new CleanRealmNodeInheritanceMessage(
                $autoRealmNode->getNode()->getId(),
                null !== $autoRealmNode->getRealm() ? $autoRealmNode->getRealm()->getId() : null
            )));
        }
    }

    private function applyRootRealmNodes(Node $node): void
    {
        /** @var NodeHandler $nodeHandler */
        $nodeHandler = $this->handlerFactory->getHandler($node);
        $parents = $nodeHandler->getParents();

        if (count($parents) === 0) {
            return;
        }

        foreach ($parents as $parent) {
            /** @var RealmNode[] $rootRealmNodes */
            $rootRealmNodes = $this->managerRegistry->getRepository(RealmNode::class)->findBy([
                'node' => $parent,
                'inheritanceType' => RealmInterface::INHERITANCE_ROOT,
            ]);
            foreach ($rootRealmNodes as $rootRealmNode) {
                $this->logger->info('Apply new root RealmNode information');
                $this->bus->dispatch(new Envelope(new ApplyRealmNodeInheritanceMessage(
                    $rootRealmNode->getNode()->getId(),
                    null !== $rootRealmNode->getRealm() ? $rootRealmNode->getRealm()->getId() : null
                )));
            }
        }
    }
}
