<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Node;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Event\Node\NodeCreatedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeDuplicatedEvent;
use RZ\Roadiz\CoreBundle\Node\NodeDuplicator;
use RZ\Roadiz\CoreBundle\Node\NodeNamePolicyInterface;
use RZ\Roadiz\CoreBundle\Repository\AllStatusesNodeRepository;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class NodeDuplicateController extends AbstractController
{
    public function __construct(
        private readonly NodeNamePolicyInterface $nodeNamePolicy,
        private readonly ManagerRegistry $managerRegistry,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly TranslatorInterface $translator,
        private readonly LogTrail $logTrail,
        private readonly AllStatusesNodeRepository $allStatusesNodeRepository,
    ) {
    }

    public function duplicateAction(Request $request, int $nodeId): Response
    {
        /** @var Node|null $existingNode */
        $existingNode = $this->allStatusesNodeRepository->find($nodeId);

        if (null === $existingNode) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted(NodeVoter::DUPLICATE, $existingNode);

        try {
            $duplicator = new NodeDuplicator(
                $existingNode,
                $this->managerRegistry->getManagerForClass(Node::class),
                $this->nodeNamePolicy
            );
            $newNode = $duplicator->duplicate();

            $this->eventDispatcher->dispatch(new NodeCreatedEvent($newNode));
            $this->eventDispatcher->dispatch(new NodeDuplicatedEvent($newNode));

            $msg = $this->translator->trans('duplicated.node.%name%', [
                '%name%' => $existingNode->getNodeName(),
            ]);

            $this->logTrail->publishConfirmMessage($request, $msg, $newNode->getNodeSources()->first() ?: $newNode);

            return $this->redirectToRoute(
                'nodesEditPage',
                ['nodeId' => $newNode->getId()]
            );
        } catch (\Exception) {
            $this->logTrail->publishErrorMessage(
                $request,
                $this->translator->trans('impossible.duplicate.node.%name%', [
                    '%name%' => $existingNode->getNodeName(),
                ]),
                $existingNode
            );

            return $this->redirectToRoute(
                'nodesEditPage',
                ['nodeId' => $existingNode->getId()]
            );
        }
    }
}
