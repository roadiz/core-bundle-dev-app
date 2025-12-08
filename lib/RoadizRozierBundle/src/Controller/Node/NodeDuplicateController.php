<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Node;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Event\Node\NodeCreatedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeDuplicatedEvent;
use RZ\Roadiz\CoreBundle\Node\NodeDuplicator;
use RZ\Roadiz\CoreBundle\Node\NodeNamePolicyInterface;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
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
    ) {
    }

    #[Route(
        path: '/rz-admin/nodes/duplicate/{nodeId}',
        name: 'nodesDuplicatePage',
        requirements: ['nodeId' => '[0-9]+'],
        methods: ['GET', 'POST'],
    )]
    public function duplicateAction(
        Request $request,
        #[MapEntity(
            expr: 'repository.find(nodeId)',
            message: 'Node does not exist'
        )]
        Node $existingNode,
    ): Response {
        $this->denyAccessUnlessGranted(NodeVoter::DUPLICATE, $existingNode);

        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('@RoadizRozier/nodes/duplicate.html.twig', [
                'node' => $existingNode,
                'form' => $form->createView(),
            ]);
        }

        try {
            $duplicator = new NodeDuplicator(
                $existingNode,
                $this->managerRegistry->getManagerForClass(Node::class) ?? throw new \RuntimeException('No object manager found for Node class.'),
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
