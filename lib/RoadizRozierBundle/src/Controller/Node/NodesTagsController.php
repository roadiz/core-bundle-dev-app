<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Node;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Event\Node\NodeTaggedEvent;
use RZ\Roadiz\CoreBundle\Node\NodeFactory;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\RozierBundle\Form\NodesTagsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Themes\Rozier\Traits\NodesTrait;

final class NodesTagsController extends AbstractController
{
    use NodesTrait;

    public function __construct(
        private readonly NodeFactory $nodeFactory,
        private readonly ManagerRegistry $managerRegistry,
        private readonly TranslatorInterface $translator,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LogTrail $logTrail,
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    /**
     * Return tags form for requested node.
     */
    public function editTagsAction(Request $request, Node $nodeId): Response
    {
        /** @var NodesSourcesRepository $nodeSourceRepository */
        $nodeSourceRepository = $this->managerRegistry->getRepository(NodesSources::class);
        $nodeSourceRepository
            ->setDisplayingAllNodesStatuses(true)
            ->setDisplayingNotPublishedNodes(true);

        /** @var NodesSources|null $source */
        $source = $nodeSourceRepository->findOneByNodeAndTranslation(
            $nodeId,
            $this->managerRegistry->getRepository(Translation::class)->findDefault()
        );

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

    protected function getNodeFactory(): NodeFactory
    {
        return $this->nodeFactory;
    }

    protected function em(): ObjectManager
    {
        return $this->managerRegistry->getManagerForClass(Node::class);
    }

    protected function createNamedFormBuilder(
        string $name = 'form',
        mixed $data = null,
        array $options = [],
    ): FormBuilderInterface {
        return $this->formFactory->createNamedBuilder(name: $name, data: $data, options: $options);
    }
}
