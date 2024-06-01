<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message\Handler;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Realm;
use RZ\Roadiz\CoreBundle\Entity\RealmNode;
use RZ\Roadiz\CoreBundle\Message\ApplyRealmNodeInheritanceMessage;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
final class ApplyRealmNodeInheritanceMessageHandler
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
    }

    public function __invoke(ApplyRealmNodeInheritanceMessage $message): void
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

        $realmNode = $this->managerRegistry->getRepository(RealmNode::class)->findOneBy([
            'node' => $node,
            'realm' => $realm,
        ]);

        /*
         * Do not propagate if realm node inheritance type is not ROOT
         */
        if (null === $realmNode || $realmNode->getInheritanceType() !== RealmInterface::INHERITANCE_ROOT) {
            return;
        }

        $nodeRepository = $this->managerRegistry->getRepository(Node::class);
        $childrenIds = $nodeRepository->findAllOffspringIdByNode($node);

        foreach ($childrenIds as $childId) {
            /** @var Node|null $child */
            $child = $nodeRepository->find($childId);
            if (null === $child) {
                continue;
            }

            /** @var RealmNode|null $childRealmNode */
            $childRealmNode = $this->managerRegistry->getRepository(RealmNode::class)->findOneBy([
                'node' => $child,
                'realm' => $realm,
            ]);
            if (null === $childRealmNode) {
                $childRealmNode = new RealmNode();
                $childRealmNode->setNode($child);
                $childRealmNode->setRealm($realm);
                $childRealmNode->setInheritanceType(RealmInterface::INHERITANCE_AUTO);
                $this->managerRegistry->getManager()->persist($childRealmNode);
            }
        }

        $this->managerRegistry->getManager()->flush();
    }
}
