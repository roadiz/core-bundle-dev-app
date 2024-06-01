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
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Themes\Rozier\RozierApp;

final class RealmNodeController extends RozierApp
{
    private ManagerRegistry $managerRegistry;
    private TranslatorInterface $translator;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        ManagerRegistry $managerRegistry,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function defaultAction(Request $request, Node $id): Response
    {
        $this->denyAccessUnlessGranted(NodeVoter::EDIT_REALMS, $id);

        $node = $id;
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
                    '%realm%' => $realmNode->getRealm()->getName()
                ]
            );
            $this->publishConfirmMessage($request, $msg);

            return $this->redirectToRoute('nodesRealmsPage', [
                'id' => $node->getId()
            ]);
        }

        $this->assignation['form'] = $form->createView();
        $this->assignation['node'] = $node;
        $this->assignation['source'] = $nodeSource;
        $this->assignation['nodeRealms'] = $this->managerRegistry
            ->getRepository(RealmNode::class)
            ->findByNode($node);
        $this->assignation['translation'] = $nodeSource->getTranslation();

        return $this->render('@RoadizRozier/nodes/realms.html.twig', $this->assignation);
    }

    public function deleteAction(Request $request, int $id, int $realmNodeId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_REALM_NODES');
        /** @var Node|null $node */
        $node = $this->managerRegistry->getRepository(Node::class)->find($id);
        /** @var RealmNode|null $realmNode */
        $realmNode = $this->managerRegistry->getRepository(RealmNode::class)->find($realmNodeId);
        if (null === $node || null === $realmNode) {
            throw new ResourceNotFoundException();
        }
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
                    '%realm%' => $realmNode->getRealm()->getName()
                ]
            );
            $this->publishConfirmMessage($request, $msg);

            return $this->redirectToRoute('nodesRealmsPage', [
                'id' => $node->getId()
            ]);
        }

        $this->assignation['form'] = $form->createView();
        $this->assignation['node'] = $node;
        $this->assignation['source'] = $nodeSource;
        $this->assignation['realmNode'] = $realmNode;
        $this->assignation['translation'] = $nodeSource->getTranslation();

        return $this->render('@RoadizRozier/nodes/deleteRealm.html.twig', $this->assignation);
    }
}
