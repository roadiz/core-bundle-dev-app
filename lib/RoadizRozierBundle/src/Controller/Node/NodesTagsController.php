<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Node;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Event\Node\NodeTaggedEvent;
use RZ\Roadiz\CoreBundle\Node\NodeFactory;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use RZ\Roadiz\RozierBundle\Form\NodesTagsType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Themes\Rozier\RozierApp;
use Themes\Rozier\Traits\NodesTrait;

class NodesTagsController extends RozierApp
{
    use NodesTrait;

    private NodeFactory $nodeFactory;

    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }

    protected function getNodeFactory(): NodeFactory
    {
        return $this->nodeFactory;
    }

    /**
     * Return tags form for requested node.
     *
     * @param Request $request
     * @param Node $nodeId
     *
     * @return Response
     * @throws \Twig\Error\RuntimeError
     */
    public function editTagsAction(Request $request, Node $nodeId): Response
    {
        /** @var NodesSourcesRepository $nodeSourceRepository */
        $nodeSourceRepository = $this->em()->getRepository(NodesSources::class);
        $nodeSourceRepository
            ->setDisplayingAllNodesStatuses(true)
            ->setDisplayingNotPublishedNodes(true);

        /** @var NodesSources|null $source */
        $source = $nodeSourceRepository->findOneByNodeAndTranslation(
            $nodeId,
            $this->em()->getRepository(Translation::class)->findDefault()
        );

        if (null === $source) {
            throw new ResourceNotFoundException();
        }

        $this->denyAccessUnlessGranted(NodeVoter::EDIT_TAGS, $source);

        $node = $source->getNode();
        $form = $this->createForm(NodesTagsType::class, $node);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /*
             * Dispatch event
             */
            $this->dispatchEvent(new NodeTaggedEvent($node));
            $this->em()->flush();

            $msg = $this->getTranslator()->trans('node.%node%.linked.tags', [
                '%node%' => $node->getNodeName(),
            ]);
            $this->publishConfirmMessage($request, $msg, $source);

            return $this->redirectToRoute(
                'nodesEditTagsPage',
                ['nodeId' => $node->getId()]
            );
        }

        $this->assignation['translation'] = $source->getTranslation();
        $this->assignation['node'] = $node;
        $this->assignation['source'] = $source;
        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/nodes/editTags.html.twig', $this->assignation);
    }
}
