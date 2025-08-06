<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message\Handler;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Message\CleanRealmNodeInheritanceMessage;
use RZ\Roadiz\CoreBundle\Node\NodeOffspringResolverInterface;
use RZ\Roadiz\CoreBundle\Repository\AllStatusesNodeRepository;
use RZ\Roadiz\CoreBundle\Repository\RealmNodeRepository;
use RZ\Roadiz\CoreBundle\Repository\RealmRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
final readonly class CleanRealmNodeInheritanceMessageHandler
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private AllStatusesNodeRepository $allStatusesNodeRepository,
        private RealmNodeRepository $realmNodeRepository,
        private RealmRepository $realmRepository,
        private NodeOffspringResolverInterface $nodeOffspringResolver,
    ) {
    }

    public function __invoke(CleanRealmNodeInheritanceMessage $message): void
    {
        if (null === $message->getRealmId()) {
            return;
        }
        $node = $this->allStatusesNodeRepository->find($message->getNodeId());
        $realm = $this->realmRepository->find($message->getRealmId());

        if (null === $node) {
            throw new UnrecoverableMessageHandlingException('Node does not exist');
        }
        if (null === $realm) {
            throw new UnrecoverableMessageHandlingException('Realm does not exist');
        }

        $childrenIds = $this->nodeOffspringResolver->getAllOffspringIds($node);
        $realmNodes = $this->realmNodeRepository->findByNodeIdsAndRealmId(
            $childrenIds,
            $message->getRealmId()
        );

        foreach ($realmNodes as $realmNode) {
            $this->managerRegistry->getManager()->remove($realmNode);
        }

        $this->managerRegistry->getManager()->flush();
    }
}
