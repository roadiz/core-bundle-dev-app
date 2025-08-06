<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Node;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Event\Node\NodeTaggedEvent;
use RZ\Roadiz\CoreBundle\Node\NodeFactory;
use RZ\Roadiz\CoreBundle\Repository\AllStatusesNodesSourcesRepository;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\RozierBundle\Controller\NodeControllerTrait;
use RZ\Roadiz\RozierBundle\Form\NodesTagsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class NodeTagController extends AbstractController
{
    use NodeControllerTrait;

    public function __construct(
        private readonly NodeFactory $nodeFactory,
        private readonly ManagerRegistry $managerRegistry,
        private readonly TranslatorInterface $translator,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LogTrail $logTrail,
        private readonly FormFactoryInterface $formFactory,
        private readonly AllStatusesNodesSourcesRepository $allStatusesNodesSourcesRepository,
    ) {
    }

    /**
     * Return tags form for requested node.
     */
    public function editTagsAction(Request $request, Node $nodeId): Response
    {
        /**
         * Get all sources because if node does not have a default translation
         * we still need to get one available translation.
         *
         * @var NodesSources|null $source
         */
        $source = $this->allStatusesNodesSourcesRepository->findByNode(
            $nodeId
        )[0] ?? null;

        if (null === $source) {
            throw new ResourceNotFoundException();
        }

        $this->denyAccessUnlessGranted(NodeVoter::EDIT_TAGS, $source);

        $node = $source->getNode();
        $form = $this->createForm(NodesTagsType::class, $node);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->eventDispatcher->dispatch(new NodeTaggedEvent($node));
            $this->managerRegistry->getManagerForClass(Node::class)->flush();

            $msg = $this->translator->trans('node.%node%.linked.tags', [
                '%node%' => $node->getNodeName(),
            ]);
            $this->logTrail->publishConfirmMessage($request, $msg, $source);

            return $this->redirectToRoute(
                'nodesEditTagsPage',
                ['nodeId' => $node->getId()]
            );
        }

        return $this->render('@RoadizRozier/nodes/editTags.html.twig', [
            'translation' => $source->getTranslation(),
            'node' => $node,
            'source' => $source,
            'form' => $form->createView(),
        ]);
    }

    #[\Override]
    protected function getNodeFactory(): NodeFactory
    {
        return $this->nodeFactory;
    }

    #[\Override]
    protected function em(): ObjectManager
    {
        return $this->managerRegistry->getManagerForClass(Node::class);
    }

    #[\Override]
    protected function createNamedFormBuilder(
        string $name = 'form',
        mixed $data = null,
        array $options = [],
    ): FormBuilderInterface {
        return $this->formFactory->createNamedBuilder(name: $name, data: $data, options: $options);
    }
}
