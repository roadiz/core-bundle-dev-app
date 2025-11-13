<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Node;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Repository\AllStatusesNodeRepository;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
use RZ\Roadiz\CoreBundle\Security\Authorization\Chroot\NodeChrootResolver;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\RozierBundle\Widget\TreeWidgetFactory;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Workflow\Registry;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class NodeTreeController extends AbstractController
{
    use NodeBulkActionTrait;

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly NodeChrootResolver $nodeChrootResolver,
        private readonly TreeWidgetFactory $treeWidgetFactory,
        private readonly FormFactoryInterface $formFactory,
        private readonly HandlerFactoryInterface $handlerFactory,
        private readonly Registry $workflowRegistry,
        private readonly LogTrail $logTrail,
        private readonly TranslatorInterface $translator,
        private readonly AllStatusesNodeRepository $allStatusesNodeRepository,
        private readonly TranslationRepository $translationRepository,
    ) {
    }

    #[Route(
        path: '/rz-admin/nodes/tree/{nodeId}/{translationId}',
        name: 'nodesTreePage',
        requirements: [
            'nodeId' => '[0-9]+',
            'translationId' => '[0-9]+',
        ],
        defaults: [
            'nodeId' => null,
            'translationId' => null,
        ],
        priority: 0,
    )]
    #[Route(
        path: '/rz-admin/nodes/tree/main/{translationId}',
        name: 'nodesMainTreePage',
        requirements: [
            'translationId' => '[0-9]+',
        ],
        defaults: [
            'nodeId' => null,
            'translationId' => null,
        ],
        priority: 1,
    )]
    public function treeAction(
        Request $request,
        #[MapEntity(
            expr: 'nodeId ? repository.find(nodeId) : null',
            message: 'Node does not exist',
        )]
        ?Node $node,
        #[MapEntity(
            expr: 'translationId ? repository.find(translationId) : repository.findDefault()',
            message: 'Translation does not exist'
        )]
        Translation $translation,
    ): Response {
        $assignation = [];

        if (null === $node && null !== $user = $this->getUser()) {
            $node = $this->nodeChrootResolver->getChroot($user);
        }

        if (null !== $node) {
            $this->denyAccessUnlessGranted(NodeVoter::READ, $node);
        } else {
            $this->denyAccessUnlessGranted(NodeVoter::READ_AT_ROOT);
        }

        $widget = $this->treeWidgetFactory->createNodeTree($node, $translation);

        if (
            $request->get('tagId')
            && $request->get('tagId') > 0
        ) {
            $filterTag = $this->managerRegistry
                ->getRepository(Tag::class)
                ->find((int) $request->get('tagId'));
            $assignation['filterTag'] = $filterTag;
            $widget->setTag($filterTag);
        }

        $widget->setStackTree(true);
        $widget->getNodes(); // pre-fetch nodes for enable filters

        if (null !== $node) {
            $assignation['node'] = $node;

            if ($node->isHidingChildren()) {
                $assignation['availableTags'] = $this->managerRegistry
                    ->getRepository(Tag::class)
                    ->findAllLinkedToNodeChildren(
                        $node,
                        $translation
                    );
            }
            $assignation['source'] = $node->getNodeSourcesByTranslation($translation)->first();
            $availableTranslations = $this->translationRepository->findAvailableTranslationsForNode($node);
            $assignation['available_translations'] = $availableTranslations;
        }
        $assignation['translation'] = $translation;
        $assignation['specificNodeTree'] = $widget;

        /*
         * Handle bulk tag form
         */
        $tagNodesForm = $this->buildBulkTagForm();
        if (null !== $response = $this->handleTagNodesForm($request, $tagNodesForm)) {
            return $response;
        }
        $assignation['tagNodesForm'] = $tagNodesForm->createView();

        /*
         * Handle bulk status
         */
        if ($this->isGranted('ROLE_ACCESS_NODES_STATUS')) {
            $statusBulkNodes = $this->buildBulkStatusForm($request->getRequestUri());
            $assignation['statusNodesForm'] = $statusBulkNodes->createView();
        }

        /*
         * Handle bulk delete form
         */
        if ($this->isGranted('ROLE_ACCESS_NODES_DELETE')) {
            $deleteNodesForm = $this->buildBulkDeleteForm($request->getRequestUri());
            $assignation['deleteNodesForm'] = $deleteNodesForm->createView();
        }

        return $this->render('@RoadizRozier/nodes/tree.html.twig', $assignation);
    }
}
