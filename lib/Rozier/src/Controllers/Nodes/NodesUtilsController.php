<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\Nodes;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Event\Node\NodeCreatedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeDuplicatedEvent;
use RZ\Roadiz\CoreBundle\Node\NodeDuplicator;
use RZ\Roadiz\CoreBundle\Node\NodeNamePolicyInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Themes\Rozier\RozierApp;

/**
 * @package Themes\Rozier\Controllers\Nodes
 */
class NodesUtilsController extends RozierApp
{
    private NodeNamePolicyInterface $nodeNamePolicy;

    /**
     * @param NodeNamePolicyInterface $nodeNamePolicy
     */
    public function __construct(NodeNamePolicyInterface $nodeNamePolicy)
    {
        $this->nodeNamePolicy = $nodeNamePolicy;
    }

    /**
     * Duplicate node by ID
     *
     * @param Request $request
     * @param int     $nodeId
     *
     * @return Response
     */
    public function duplicateAction(Request $request, int $nodeId)
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODES');

        /** @var Node $existingNode */
        $existingNode = $this->em()->find(Node::class, $nodeId);

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

            $msg = $this->getTranslator()->trans("duplicated.node.%name%", [
                '%name%' => $existingNode->getNodeName(),
            ]);

            $this->publishConfirmMessage($request, $msg, $newNode->getNodeSources()->first() ?: null);

            return $this->redirectToRoute(
                'nodesEditPage',
                ["nodeId" => $newNode->getId()]
            );
        } catch (\Exception $e) {
            $this->publishErrorMessage(
                $request,
                $this->getTranslator()->trans("impossible.duplicate.node.%name%", [
                    '%name%' => $existingNode->getNodeName(),
                ])
            );

            return $this->redirectToRoute(
                'nodesEditPage',
                ["nodeId" => $existingNode->getId()]
            );
        }
    }
}
