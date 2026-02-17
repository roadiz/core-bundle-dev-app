<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Node;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Bag\DecoratedNodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Event\Node\NodeUpdatedEvent;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesUpdatedEvent;
use RZ\Roadiz\CoreBundle\Node\NodeTranstyper;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\RozierBundle\Form\TranstypeType;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Error\RuntimeError;

#[AsController]
final class TranstypeController extends AbstractController
{
    public function __construct(
        private readonly NodeTranstyper $nodeTranstyper,
        private readonly DecoratedNodeTypes $nodeTypesBag,
        private readonly ManagerRegistry $managerRegistry,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly TranslatorInterface $translator,
        private readonly LogTrail $logTrail,
    ) {
    }

    /**
     * @throws RuntimeError
     * @throws \Exception
     */
    #[Route(
        path: '/rz-admin/nodes/edit/{nodeId}/transtype',
        name: 'nodesTranstypePage',
        requirements: [
            'nodeId' => '[0-9]+',
        ],
    )]
    public function transtypeAction(
        Request $request,
        #[MapEntity(
            expr: 'repository.find(nodeId)',
            evictCache: true,
            message: 'Node does not exist',
        )]
        Node $node,
    ): Response {
        /*
         * Transtype is only available for higher rank users
         */
        $this->denyAccessUnlessGranted(NodeVoter::EDIT_SETTING, $node);

        $form = $this->createForm(TranstypeType::class, null, [
            'currentType' => $this->nodeTypesBag->get($node->getNodeTypeName()),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $newNodeType = $this->nodeTypesBag->get($data['nodeTypeName']);
            if (null === $newNodeType) {
                throw new \RuntimeException('Selected node type does not exist.');
            }

            /*
             * Trans-typing SHOULD be executed in one single transaction
             * @see https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/transactions-and-concurrency.html
             */
            $manager = $this->managerRegistry->getManagerForClass(Node::class);
            if (null === $manager) {
                throw new \RuntimeException('No entity manager found for Node class.');
            }
            $manager->getConnection()->beginTransaction(); // suspend auto-commit
            try {
                $this->nodeTranstyper->transtype($node, $newNodeType);
                $manager->flush();
                $manager->getConnection()->commit();
            } catch (\Exception $e) {
                $manager->getConnection()->rollBack();
                throw $e;
            }

            $manager->refresh($node);
            $this->eventDispatcher->dispatch(new NodeUpdatedEvent($node));

            foreach ($node->getNodeSources() as $nodeSource) {
                $this->eventDispatcher->dispatch(new NodesSourcesUpdatedEvent($nodeSource));
            }

            $msg = $this->translator->trans('%node%.transtyped_to.%type%', [
                '%node%' => $node->getNodeName(),
                '%type%' => $newNodeType->getName(),
            ]);
            $this->logTrail->publishConfirmMessage($request, $msg, $node->getNodeSources()->first() ?: $node);

            return $this->redirectToRoute(
                'nodesEditSourcePage',
                [
                    'nodeId' => $node->getId(),
                    'translationId' => $node->getNodeSources()->first() ?
                        $node->getNodeSources()->first()->getTranslation()->getId() :
                        null,
                ]
            );
        }

        return $this->render('@RoadizRozier/admin/confirm_action.html.twig', [
            'title' => $this->translator->trans('transtype.a.node'),
            'headPath' => '@RoadizRozier/nodes/head.html.twig',
            'action_icon' => 'rz-icon-ri--command-line',
            'action_color' => 'success',
            'action_label' => 'transtype.node',
            'cancelPath' => $this->generateUrl('nodesEditPage', ['nodeId' => $node->getId()]),
            'messageType' => 'warning',
            'alertMessage' => 'transtype_will_copy_data_from_fields_existing_in_both_types_not_others',
            'form' => $form->createView(),
            'items' => [$node],
        ]);
    }
}
