<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Node;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Bag\DecoratedNodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Event\Node\NodeUpdatedEvent;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesUpdatedEvent;
use RZ\Roadiz\CoreBundle\Node\NodeTranstyper;
use RZ\Roadiz\CoreBundle\Repository\AllStatusesNodeRepository;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\RozierBundle\Form\TranstypeType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
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
        private readonly AllStatusesNodeRepository $allStatusesNodeRepository,
    ) {
    }

    /**
     * @throws RuntimeError
     * @throws \Exception
     */
    public function transtypeAction(Request $request, int $nodeId): Response
    {
        /** @var Node|null $node */
        $node = $this->allStatusesNodeRepository->find($nodeId);
        $manager = $this->managerRegistry->getManagerForClass(Node::class);
        $manager->refresh($node);

        if (null === $node) {
            throw new ResourceNotFoundException();
        }

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

            /*
             * Trans-typing SHOULD be executed in one single transaction
             * @see https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/transactions-and-concurrency.html
             */
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

        return $this->render('@RoadizRozier/nodes/transtype.html.twig', [
            'node' => $node,
            'form' => $form->createView(),
            'parentNode' => $node->getParent(),
            'type' => $node->getNodeTypeName(),
        ]);
    }
}
