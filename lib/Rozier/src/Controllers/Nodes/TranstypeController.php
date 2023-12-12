<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\Nodes;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Event\Node\NodeUpdatedEvent;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesUpdatedEvent;
use RZ\Roadiz\CoreBundle\Node\NodeTranstyper;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Themes\Rozier\Forms\TranstypeType;
use Themes\Rozier\RozierApp;
use Twig\Error\RuntimeError;

class TranstypeController extends RozierApp
{
    private NodeTranstyper $nodeTranstyper;

    /**
     * @param NodeTranstyper $nodeTranstyper
     */
    public function __construct(NodeTranstyper $nodeTranstyper)
    {
        $this->nodeTranstyper = $nodeTranstyper;
    }

    /**
     * @param Request $request
     * @param int $nodeId
     *
     * @return Response
     * @throws RuntimeError
     * @throws \Exception
     */
    public function transtypeAction(Request $request, int $nodeId): Response
    {
        /** @var Node|null $node */
        $node = $this->em()->find(Node::class, $nodeId);
        $this->em()->refresh($node);

        if (null === $node) {
            throw new ResourceNotFoundException();
        }

        /*
         * Transtype is only available for higher rank users
         */
        $this->denyAccessUnlessGranted(NodeVoter::EDIT_SETTING, $node);

        $form = $this->createForm(TranstypeType::class, null, [
            'currentType' => $node->getNodeType(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            /** @var NodeType $newNodeType */
            $newNodeType = $this->em()->find(NodeType::class, (int) $data['nodeTypeId']);

            /*
             * Trans-typing SHOULD be executed in one single transaction
             * @see https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/transactions-and-concurrency.html
             */
            $this->em()->getConnection()->beginTransaction(); // suspend auto-commit
            try {
                $this->nodeTranstyper->transtype($node, $newNodeType);
                $this->em()->flush();
                $this->em()->getConnection()->commit();
            } catch (\Exception $e) {
                $this->em()->getConnection()->rollBack();
                throw $e;
            }

            $this->em()->refresh($node);
            /*
             * Dispatch event
             */
            $this->dispatchEvent(new NodeUpdatedEvent($node));

            foreach ($node->getNodeSources() as $nodeSource) {
                $this->dispatchEvent(new NodesSourcesUpdatedEvent($nodeSource));
            }

            $msg = $this->getTranslator()->trans('%node%.transtyped_to.%type%', [
                '%node%' => $node->getNodeName(),
                '%type%' => $newNodeType->getName(),
            ]);
            $this->publishConfirmMessage($request, $msg, $node->getNodeSources()->first() ?: $node);

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

        $this->assignation['form'] = $form->createView();
        $this->assignation['node'] = $node;
        $this->assignation['parentNode'] = $node->getParent();
        $this->assignation['type'] = $node->getNodeType();

        return $this->render('@RoadizRozier/nodes/transtype.html.twig', $this->assignation);
    }
}
