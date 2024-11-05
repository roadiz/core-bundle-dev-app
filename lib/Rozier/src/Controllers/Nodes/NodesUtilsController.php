<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\Nodes;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Event\Node\NodeCreatedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeDuplicatedEvent;
use RZ\Roadiz\CoreBundle\Node\NodeDuplicator;
use RZ\Roadiz\CoreBundle\Node\NodeNamePolicyInterface;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Themes\Rozier\RozierApp;

class NodesUtilsController extends RozierApp
{
    public function __construct(private readonly NodeNamePolicyInterface $nodeNamePolicy)
    {
    }

    /**
     * Duplicate node by ID.
     */
    public function duplicateAction(Request $request, int $nodeId): Response
    {
        /** @var Node|null $existingNode */
        $existingNode = $this->em()->find(Node::class, $nodeId);

        if (null === $existingNode) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted(NodeVoter::DUPLICATE, $existingNode);

        try {
            $duplicator = new NodeDuplicator(
                $existingNode,
                $this->em(),
                $this->nodeNamePolicy
            );
            $newNode = $duplicator->duplicate();

            /*
             * Dispatch event
             */
            $this->dispatchEvent(new NodeCreatedEvent($newNode));
            $this->dispatchEvent(new NodeDuplicatedEvent($newNode));

            $msg = $this->getTranslator()->trans('duplicated.node.%name%', [
                '%name%' => $existingNode->getNodeName(),
            ]);

            $this->publishConfirmMessage($request, $msg, $newNode->getNodeSources()->first() ?: $newNode);

            return $this->redirectToRoute(
                'nodesEditPage',
                ['nodeId' => $newNode->getId()]
            );
        } catch (\Exception $e) {
            $this->publishErrorMessage(
                $request,
                $this->getTranslator()->trans('impossible.duplicate.node.%name%', [
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
