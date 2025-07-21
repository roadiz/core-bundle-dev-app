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
use RZ\Roadiz\CoreBundle\Repository\AllStatusesNodeRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class SearchRealmNodeInheritanceMessageHandler
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private HandlerFactoryInterface $handlerFactory,
        private AllStatusesNodeRepository $allStatusesNodeRepository,
        private MessageBusInterface $bus,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(SearchRealmNodeInheritanceMessage $message): void
    {
        /** @var Node|null $node */
        $node = $this->allStatusesNodeRepository->find($message->getNodeId());
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
            'inheritanceType' => RealmInterface::INHERITANCE_AUTO,
        ]);

        /*
         * If there are existing auto realmNode from former ancestor, we need to clean them
         */
        foreach ($autoRealmNodes as $autoRealmNode) {
            $this->logger->info('Clean existing RealmNode information');
            $this->bus->dispatch(new Envelope(new CleanRealmNodeInheritanceMessage(
                $autoRealmNode->getNode()->getId(),
                $autoRealmNode->getRealm()->getId()
            )));
        }
    }

    private function applyRootRealmNodes(Node $node): void
    {
        /** @var NodeHandler $nodeHandler */
        $nodeHandler = $this->handlerFactory->getHandler($node);
        $parents = $nodeHandler->getParents();

        if (0 === count($parents)) {
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
                    $rootRealmNode->getRealm()->getId()
                )));
            }
        }
    }
}
