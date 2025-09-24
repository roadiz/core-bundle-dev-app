<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Node;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Bag\DecoratedNodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeHandler;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Event\Node\NodeCreatedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeDeletedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodePathChangedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeUndeletedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeUpdatedEvent;
use RZ\Roadiz\CoreBundle\Exception\EntityAlreadyExistsException;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerFactoryInterface;
use RZ\Roadiz\CoreBundle\ListManager\SessionListFilters;
use RZ\Roadiz\CoreBundle\Node\Exception\SameNodeUrlException;
use RZ\Roadiz\CoreBundle\Node\NodeFactory;
use RZ\Roadiz\CoreBundle\Node\NodeMover;
use RZ\Roadiz\CoreBundle\Node\NodeOffspringResolverInterface;
use RZ\Roadiz\CoreBundle\Node\UniqueNodeGenerator;
use RZ\Roadiz\CoreBundle\Repository\AllStatusesNodeRepository;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
use RZ\Roadiz\CoreBundle\Security\Authorization\Chroot\NodeChrootResolver;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\RozierBundle\Breadcrumbs\BreadcrumbsItemFactoryInterface;
use RZ\Roadiz\RozierBundle\Controller\NodeControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Workflow\Registry;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Error\RuntimeError;

#[AsController]
final class NodeController extends AbstractController
{
    use NodeControllerTrait;
    use NodeBulkActionTrait;

    /**
     * @param class-string<AbstractType> $nodeFormTypeClass
     * @param class-string<AbstractType> $addNodeFormTypeClass
     */
    public function __construct(
        private readonly NodeChrootResolver $nodeChrootResolver,
        private readonly NodeMover $nodeMover,
        private readonly Registry $workflowRegistry,
        private readonly HandlerFactoryInterface $handlerFactory,
        private readonly UniqueNodeGenerator $uniqueNodeGenerator,
        private readonly NodeFactory $nodeFactory,
        private readonly NodeOffspringResolverInterface $nodeOffspringResolver,
        private readonly DecoratedNodeTypes $nodeTypesBag,
        private readonly EntityListManagerFactoryInterface $entityListManagerFactory,
        private readonly ManagerRegistry $managerRegistry,
        private readonly TranslatorInterface $translator,
        private readonly LogTrail $logTrail,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly FormFactoryInterface $formFactory,
        private readonly AllStatusesNodeRepository $allStatusesNodeRepository,
        private readonly TranslationRepository $translationRepository,
        private readonly BreadcrumbsItemFactoryInterface $breadcrumbsItemFactory,
        private readonly string $nodeFormTypeClass,
        private readonly string $addNodeFormTypeClass,
    ) {
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

    public function indexAction(Request $request, ?string $filter = null): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODES');
        $translation = $this->translationRepository->findDefault();

        /** @var User|null $user */
        $user = $this->getUser();

        $assignation = [];

        switch ($filter) {
            case 'draft':
                $assignation['mainFilter'] = $filter;
                $arrayFilter = [
                    'status' => NodeStatus::DRAFT,
                ];
                break;
            case 'pending':
                $assignation['mainFilter'] = $filter;
                $arrayFilter = [
                    'status' => NodeStatus::PENDING,
                ];
                break;
            case 'archived':
                $assignation['mainFilter'] = $filter;
                $arrayFilter = [
                    'status' => NodeStatus::ARCHIVED,
                ];
                break;
            case 'deleted':
                $assignation['mainFilter'] = $filter;
                $arrayFilter = [
                    'status' => NodeStatus::DELETED,
                ];
                break;
            case 'shadow':
                $assignation['mainFilter'] = $filter;
                $arrayFilter = [
                    'shadow' => true,
                ];
                break;
            default:
                $assignation['mainFilter'] = 'all';
                $arrayFilter = [];
                break;
        }

        if (null !== $user) {
            $arrayFilter['chroot'] = $this->nodeChrootResolver->getChroot($user);
        }

        $listManager = $this->entityListManagerFactory->createAdminEntityListManager(
            Node::class,
            $arrayFilter
        );
        $listManager->setDisplayingNotPublishedNodes(true);
        $listManager->setDisplayingAllNodesStatuses(true);
        $sessionListFilter = new SessionListFilters('node_list_item_per_page');
        $sessionListFilter->handleItemPerPage($request, $listManager);
        $listManager->handle();

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
        if ('deleted' !== $filter && $this->isGranted('ROLE_ACCESS_NODES_DELETE')) {
            $deleteNodesForm = $this->buildBulkDeleteForm($request->getRequestUri());
            $assignation['deleteNodesForm'] = $deleteNodesForm->createView();
        }

        $assignation['filters'] = $listManager->getAssignation();
        $assignation['translation'] = $translation;
        $assignation['availableTranslations'] = $this->translationRepository->findAll();
        $assignation['nodes'] = $listManager->getEntities();
        $assignation['nodeTypes'] = $this->nodeTypesBag->allVisible();

        return $this->render('@RoadizRozier/nodes/list.html.twig', $assignation);
    }

    public function editAction(Request $request, int $nodeId, ?int $translationId = null): Response
    {
        $assignation = [];

        /** @var Node|null $node */
        $node = $this->allStatusesNodeRepository->find($nodeId);
        if (null === $node) {
            throw new ResourceNotFoundException(sprintf('Node #%s does not exist.', $nodeId));
        }

        $this->denyAccessUnlessGranted(NodeVoter::EDIT_SETTING, $node);
        $this->managerRegistry->getManager()->refresh($node);

        $stackTypesForm = $this->buildStackTypesForm($node);
        if (null !== $stackTypesForm) {
            $stackTypesForm->handleRequest($request);
            if ($stackTypesForm->isSubmitted() && $stackTypesForm->isValid()) {
                try {
                    $type = $this->addStackType($stackTypesForm->getData(), $node, $this->nodeTypesBag);
                    $msg = $this->translator->trans(
                        'stack_node.%name%.has_new_type.%type%',
                        [
                            '%name%' => $node->getNodeName(),
                            '%type%' => $type->getDisplayName(),
                        ]
                    );
                    $this->logTrail->publishConfirmMessage($request, $msg, $node);

                    return $this->redirectToRoute(
                        'nodesEditPage',
                        ['nodeId' => $node->getId()]
                    );
                } catch (EntityAlreadyExistsException $e) {
                    $stackTypesForm->addError(new FormError($e->getMessage()));
                }
            }
            $assignation['stackTypesForm'] = $stackTypesForm->createView();
        }

        $form = $this->createForm($this->nodeFormTypeClass, $node, [
            'nodeName' => $node->getNodeName(),
        ]);
        try {
            if ($this->nodeTypesBag->get($node->getNodeTypeName())?->isReachable() && !$node->isHome()) {
                $oldPaths = $this->nodeMover->getNodeSourcesUrls($node);
            }
        } catch (SameNodeUrlException $e) {
            $oldPaths = [];
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->managerRegistry->getManager()->flush();

                if (isset($oldPaths) && count($oldPaths) > 0 && !$node->isHome()) {
                    $this->eventDispatcher->dispatch(new NodePathChangedEvent($node, $oldPaths));
                }
                $this->eventDispatcher->dispatch(new NodeUpdatedEvent($node));
                $this->managerRegistry->getManager()->flush();
                $msg = $this->translator->trans('node.%name%.updated', [
                    '%name%' => $node->getNodeName(),
                ]);
                $this->logTrail->publishConfirmMessage($request, $msg, $node->getNodeSources()->first() ?: $node);

                return $this->redirectToRoute(
                    'nodesEditPage',
                    ['nodeId' => $node->getId()]
                );
            } catch (EntityAlreadyExistsException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        $translation = $this->translationRepository->findDefault();
        $source = $node->getNodeSourcesByTranslation($translation)->first() ?: null;

        if (null === $source) {
            $availableTranslations = $this->translationRepository->findAvailableTranslationsForNode($node);
            $assignation['available_translations'] = $availableTranslations;
        }
        $assignation['node'] = $node;
        $assignation['source'] = $source;
        $assignation['translation'] = $translation;
        $assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/nodes/edit.html.twig', $assignation);
    }

    public function removeStackTypeAction(Request $request, int $nodeId, string $typeName): Response
    {
        /** @var Node|null $node */
        $node = $this->allStatusesNodeRepository->find($nodeId);
        if (null === $node) {
            throw new ResourceNotFoundException(sprintf('Node #%s does not exist.', $nodeId));
        }
        $this->denyAccessUnlessGranted(NodeVoter::EDIT_SETTING, $node);

        $type = $this->nodeTypesBag->get($typeName);
        if (null === $type) {
            throw new ResourceNotFoundException(sprintf('NodeType #%s does not exist.', $typeName));
        }

        $node->removeStackType($type);
        $this->managerRegistry->getManager()->flush();

        $msg = $this->translator->trans(
            'stack_type.%type%.has_been_removed.%name%',
            [
                '%name%' => $node->getNodeName(),
                '%type%' => $type->getDisplayName(),
            ]
        );
        $this->logTrail->publishConfirmMessage($request, $msg, $node->getNodeSources()->first() ?: null);

        return $this->redirectToRoute('nodesEditPage', ['nodeId' => $node->getId()]);
    }

    public function addAction(Request $request, ?int $translationId = null): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODES');

        /** @var Translation|null $translation */
        $translation = $this->translationRepository->findDefault();

        if (null !== $translationId) {
            $translation = $this->translationRepository->find($translationId);
        }
        if (null === $translation) {
            throw new ResourceNotFoundException(sprintf('Translation #%s does not exist.', $translationId));
        }

        $node = new Node();

        $chroot = $this->nodeChrootResolver->getChroot($this->getUser());
        if (null !== $chroot) {
            // If user is jailed in a node, prevent moving nodes out.
            $node->setParent($chroot);
        }

        $form = $this->createForm($this->addNodeFormTypeClass, $node, [
            'nodeName' => '',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $node = $this->createNode($form->get('title')->getData(), $translation, $node);
                $this->managerRegistry->getManager()->refresh($node);
                $this->eventDispatcher->dispatch(new NodeCreatedEvent($node));

                /** @var NodeType $nodeType */
                $nodeType = $form->get('nodeTypeName')->getData();
                if (null === $nodeType) {
                    throw new ResourceNotFoundException(sprintf('Node-type #%s does not exist.', $nodeType->getName()));
                }
                $node->setNodeTypeName($nodeType->getName());
                $node->setTtl($nodeType->getDefaultTtl());

                $msg = $this->translator->trans(
                    'node.%name%.created',
                    ['%name%' => $node->getNodeName()]
                );
                $this->logTrail->publishConfirmMessage($request, $msg, $node->getNodeSources()->first() ?: null);

                return $this->redirectToRoute(
                    'nodesEditSourcePage',
                    [
                        'nodeId' => $node->getId(),
                        'translationId' => $translation->getId(),
                    ]
                );
            } catch (EntityAlreadyExistsException|\InvalidArgumentException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('@RoadizRozier/nodes/add.html.twig', [
            'translation' => $translation,
            'form' => $form->createView(),
            'nodeTypesCount' => $this->nodeTypesBag->count(),
        ]);
    }

    public function addChildAction(Request $request, ?int $nodeId = null, ?int $translationId = null): Response
    {
        /** @var Translation|null $translation */
        $translation = $this->translationRepository->findDefault();
        $nodeTypesCount = $this->nodeTypesBag->count();

        if (null !== $translationId) {
            /** @var Translation|null $translation */
            $translation = $this->translationRepository->find($translationId);
        }

        if (null === $translation) {
            throw new ResourceNotFoundException('Translation does not exist');
        }

        if (null !== $nodeId && $nodeId > 0) {
            /** @var Node|null $parentNode */
            $parentNode = $this->allStatusesNodeRepository->find($nodeId);
            if (null === $parentNode) {
                throw new ResourceNotFoundException(sprintf('Node #%s does not exist.', $nodeId));
            }
            $this->denyAccessUnlessGranted(NodeVoter::CREATE, $parentNode);
        } else {
            $parentNode = null;
            $this->denyAccessUnlessGranted(NodeVoter::CREATE_AT_ROOT);
        }

        $node = new Node();
        if (null !== $parentNode) {
            $node->setParent($parentNode);
        }

        $form = $this->createForm($this->addNodeFormTypeClass, $node, [
            'nodeName' => '',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $node = $this->createNode($form->get('title')->getData(), $translation, $node);
                $this->managerRegistry->getManager()->refresh($node);

                $this->eventDispatcher->dispatch(new NodeCreatedEvent($node));

                $msg = $this->translator->trans(
                    'child_node.%name%.created',
                    ['%name%' => $node->getNodeName()]
                );
                $this->logTrail->publishConfirmMessage($request, $msg, $node->getNodeSources()->first() ?: null);

                return $this->redirectToRoute(
                    'nodesEditSourcePage',
                    [
                        'nodeId' => $node->getId(),
                        'translationId' => $translation->getId(),
                    ]
                );
            } catch (EntityAlreadyExistsException|\InvalidArgumentException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('@RoadizRozier/nodes/add.html.twig', [
            'translation' => $translation,
            'form' => $form->createView(),
            'parentNode' => $parentNode,
            'nodeTypesCount' => $nodeTypesCount,
        ]);
    }

    public function deleteAction(Request $request, int $nodeId): Response
    {
        /** @var Node|null $node */
        $node = $this->allStatusesNodeRepository->find($nodeId);

        if (null === $node) {
            throw new ResourceNotFoundException(sprintf('Node #%s does not exist.', $nodeId));
        }

        $this->denyAccessUnlessGranted(NodeVoter::DELETE, $node);

        $workflow = $this->workflowRegistry->get($node);
        if (!$workflow->can($node, 'delete')) {
            $this->logTrail->publishErrorMessage($request, sprintf('Node #%s cannot be deleted.', $nodeId), $node);

            return $this->redirectToRoute(
                'nodesEditSourcePage',
                [
                    'nodeId' => $node->getId(),
                    'translationId' => $this->translationRepository->findDefault()->getId(),
                ]
            );
        }

        $form = $this->buildDeleteForm($node);
        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
            && $form->getData()['nodeId'] == $node->getId()
        ) {
            /** @var Node|null $parent */
            $parent = $node->getParent();

            $this->eventDispatcher->dispatch(new NodeDeletedEvent($node));

            /** @var NodeHandler $nodeHandler */
            $nodeHandler = $this->handlerFactory->getHandler($node);
            $nodeHandler->softRemoveWithChildren();
            $this->managerRegistry->getManager()->flush();

            $msg = $this->translator->trans(
                'node.%name%.deleted',
                ['%name%' => $node->getNodeName()]
            );
            $this->logTrail->publishConfirmMessage($request, $msg, $node->getNodeSources()->first() ?: $node);

            $referrer = $request->query->get('referer');
            if (
                \is_string($referrer)
                && (new UnicodeString($referrer))->trim()->startsWith('/')
            ) {
                return $this->redirect($referrer);
            }
            if (null !== $parent) {
                $breadcrumbsItem = $this->breadcrumbsItemFactory->createBreadcrumbsItem($parent);
                if (null !== $breadcrumbsItem) {
                    return $this->redirect(
                        $breadcrumbsItem->url
                    );
                }

                return $this->redirectToRoute(
                    'nodesEditSourcePage',
                    [
                        'nodeId' => $parent->getId(),
                        'translationId' => $this->translationRepository->findDefault()->getId(),
                    ]
                );
            }

            return $this->redirectToRoute('nodesHomePage');
        }

        return $this->render('@RoadizRozier/nodes/delete.html.twig', [
            'form' => $form->createView(),
            'node' => $node,
        ]);
    }

    public function emptyTrashAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted(NodeVoter::EMPTY_TRASH);

        $form = $this->buildEmptyTrashForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = ['status' => NodeStatus::DELETED];
            /** @var Node|null $chroot */
            $chroot = $this->nodeChrootResolver->getChroot($this->getUser());
            if (null !== $chroot) {
                $criteria['parent'] = $this->nodeOffspringResolver->getAllOffspringIds($chroot);
            }

            $nodes = $this->allStatusesNodeRepository->findBy($criteria);

            /** @var Node $node */
            foreach ($nodes as $node) {
                /** @var NodeHandler $nodeHandler */
                $nodeHandler = $this->handlerFactory->getHandler($node);
                $nodeHandler->removeWithChildrenAndAssociations();
            }

            $this->managerRegistry->getManager()->flush();

            $msg = $this->translator->trans('node.trash.emptied');
            $this->logTrail->publishConfirmMessage($request, $msg);

            return $this->redirectToRoute('nodesHomeDeletedPage');
        }

        return $this->render('@RoadizRozier/nodes/emptyTrash.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function undeleteAction(Request $request, int $nodeId): Response
    {
        /** @var Node|null $node */
        $node = $this->allStatusesNodeRepository->find($nodeId);

        if (null === $node) {
            throw new ResourceNotFoundException(sprintf('Node #%s does not exist.', $nodeId));
        }

        $this->denyAccessUnlessGranted(NodeVoter::DELETE, $node);

        $workflow = $this->workflowRegistry->get($node);
        if (!$workflow->can($node, 'undelete')) {
            $this->logTrail->publishErrorMessage($request, sprintf('Node #%s cannot be undeleted.', $nodeId), $node);

            return $this->redirectToRoute(
                'nodesEditSourcePage',
                [
                    'nodeId' => $node->getId(),
                    'translationId' => $this->translationRepository->findDefault()->getId(),
                ]
            );
        }
        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->eventDispatcher->dispatch(new NodeUndeletedEvent($node));

            /** @var NodeHandler $nodeHandler */
            $nodeHandler = $this->handlerFactory->getHandler($node);
            $nodeHandler->softUnremoveWithChildren();
            $this->managerRegistry->getManager()->flush();

            $msg = $this->translator->trans(
                'node.%name%.undeleted',
                ['%name%' => $node->getNodeName()]
            );
            $this->logTrail->publishConfirmMessage($request, $msg, $node->getNodeSources()->first() ?: $node);

            return $this->redirectToRoute('nodesEditPage', [
                'nodeId' => $node->getId(),
            ]);
        }

        return $this->render('@RoadizRozier/nodes/undelete.html.twig', [
            'node' => $node,
            'form' => $form->createView(),
        ]);
    }

    public function generateAndAddNodeAction(Request $request): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODES');

        try {
            $source = $this->uniqueNodeGenerator->generateFromRequest($request);
            /** @var Translation $translation */
            $translation = $source->getTranslation();

            $this->eventDispatcher->dispatch(new NodeCreatedEvent($source->getNode()));

            return $this->redirectToRoute(
                'nodesEditSourcePage',
                [
                    'nodeId' => $source->getNode()->getId(),
                    'translationId' => $translation->getId(),
                ]
            );
        } catch (\Exception) {
            $msg = $this->translator->trans('node.noCreation.alreadyExists');
            throw new ResourceNotFoundException($msg);
        }
    }

    /**
     * @throws RuntimeError
     */
    public function publishAllAction(Request $request, int $nodeId): Response
    {
        /** @var Node|null $node */
        $node = $this->allStatusesNodeRepository->find($nodeId);

        if (null === $node) {
            throw new ResourceNotFoundException(sprintf('Node #%s does not exist.', $nodeId));
        }
        $this->denyAccessUnlessGranted(NodeVoter::EDIT_STATUS, $node);

        $workflow = $this->workflowRegistry->get($node);
        if (!$workflow->can($node, 'publish')) {
            $this->logTrail->publishErrorMessage($request, sprintf('Node #%s cannot be published.', $nodeId), $node);

            return $this->redirectToRoute(
                'nodesEditSourcePage',
                [
                    'nodeId' => $node->getId(),
                    'translationId' => $this->translationRepository->findDefault()->getId(),
                ]
            );
        }

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var NodeHandler $nodeHandler */
            $nodeHandler = $this->handlerFactory->getHandler($node);
            $nodeHandler->publishWithChildren();
            $this->managerRegistry->getManager()->flush();

            $msg = $this->translator->trans('node.offspring.published');
            $this->logTrail->publishConfirmMessage($request, $msg, $node);

            return $this->redirectToRoute('nodesEditSourcePage', [
                'nodeId' => $nodeId,
                'translationId' => $node->getNodeSources()->first() ?
                    $node->getNodeSources()->first()->getTranslation()->getId() :
                    null,
            ]);
        }

        return $this->render('@RoadizRozier/nodes/publishAll.html.twig', [
            'node' => $node,
            'form' => $form->createView(),
        ]);
    }
}
