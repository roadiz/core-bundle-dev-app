<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Node;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\RealmNode;
use RZ\Roadiz\CoreBundle\Event\Realm\NodeJoinedRealmEvent;
use RZ\Roadiz\CoreBundle\Event\Realm\NodeLeftRealmEvent;
use RZ\Roadiz\CoreBundle\Form\RealmNodeType;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class RealmNodeController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly TranslatorInterface $translator,
        private readonly LogTrail $logTrail,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    #[Route(
        path: '/rz-admin/nodes/realms/{id}',
        name: 'nodesRealmsPage',
        requirements: [
            'id' => '[0-9]+',
        ],
    )]
    public function defaultAction(
        Request $request,
        #[MapEntity(
            expr: 'repository.find(id)',
            message: 'Node does not exist'
        )]
        Node $node,
    ): Response {
        $this->denyAccessUnlessGranted(NodeVoter::EDIT_REALMS, $node);

        $realmNode = new RealmNode();
        $realmNode->setNode($node);
        $realmNode->setInheritanceType(RealmInterface::INHERITANCE_ROOT);
        $nodeSource = $node->getNodeSources()->first();
        if (!$nodeSource instanceof NodesSources) {
            throw new ResourceNotFoundException();
        }

        $form = $this->createForm(RealmNodeType::class, $realmNode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->managerRegistry->getManager()->persist($realmNode);

            // Dispatch event before flush to apply any DB changes occurring in subscribers
            $this->eventDispatcher->dispatch(new NodeJoinedRealmEvent($realmNode));

            $this->managerRegistry->getManager()->flush();

            $msg = $this->translator->trans(
                'node.%node%.joined.%realm%',
                [
                    '%node%' => $nodeSource->getTitle(),
                    '%realm%' => $realmNode->getRealm()->getName(),
                ]
            );
            $this->logTrail->publishConfirmMessage($request, $msg);

            return $this->redirectToRoute('nodesRealmsPage', [
                'id' => $node->getId(),
            ]);
        }

        return $this->render('@RoadizRozier/nodes/realms.html.twig', [
            'node' => $node,
            'source' => $nodeSource,
            'form' => $form->createView(),
            'translation' => $nodeSource->getTranslation(),
            'nodeRealms' => $this->managerRegistry
                ->getRepository(RealmNode::class)
                ->findByNode($node),
        ]);
    }

    #[Route(
        path: '/rz-admin/nodes/realms/{id}/delete/{realmNodeId}',
        name: 'nodesRealmsDeletePage',
        requirements: [
            'id' => '[0-9]+',
            'realmNodeId' => '[0-9]+',
        ],
    )]
    public function deleteAction(
        Request $request,
        #[MapEntity(
            expr: 'repository.find(id)',
            message: 'Node does not exist'
        )]
        Node $node,
        #[MapEntity(
            expr: 'repository.find(realmNodeId)',
            message: 'Realm node does not exist'
        )]
        RealmNode $realmNode,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_REALM_NODES');

        $nodeSource = $node->getNodeSources()->first();
        if (!$nodeSource instanceof NodesSources) {
            throw new ResourceNotFoundException();
        }

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->managerRegistry->getManager()->remove($realmNode);

            // Dispatch event before flush to apply any DB changes occurring in subscribers
            $this->eventDispatcher->dispatch(new NodeLeftRealmEvent($realmNode));

            $this->managerRegistry->getManager()->flush();

            $msg = $this->translator->trans(
                'node.%node%.left.%realm%',
                [
                    '%node%' => $nodeSource->getTitle(),
                    '%realm%' => $realmNode->getRealm()->getName(),
                ]
            );
            $this->logTrail->publishConfirmMessage($request, $msg);

            return $this->redirectToRoute('nodesRealmsPage', [
                'id' => $node->getId(),
            ]);
        }

        return $this->render('@RoadizRozier/nodes/deleteRealm.html.twig', [
            'form' => $form->createView(),
            'node' => $node,
            'source' => $nodeSource,
            'realmNode' => $realmNode,
            'translation' => $nodeSource->getTranslation(),
        ]);
    }
}
