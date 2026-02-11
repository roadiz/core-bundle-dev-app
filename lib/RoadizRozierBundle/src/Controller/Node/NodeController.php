<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Node;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
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
use RZ\Roadiz\CoreBundle\Explorer\ExplorerItemFactoryInterface;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerFactoryInterface;
use RZ\Roadiz\CoreBundle\ListManager\SessionListFilters;
use RZ\Roadiz\CoreBundle\Model\NodeCreationDto;
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
use RZ\Roadiz\RozierBundle\Controller\Ajax\AbstractAjaxController;
use RZ\Roadiz\RozierBundle\Controller\NodeControllerTrait;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Workflow\Registry;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
        private readonly ExplorerItemFactoryInterface $explorerItemFactory,
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
        return $this->managerRegistry->getManagerForClass(Node::class) ?? throw new \RuntimeException('No object manager found for Node class.');
    }

    #[\Override]
    protected function createNamedFormBuilder(
        string $name = 'form',
        mixed $data = null,
        array $options = [],
    ): FormBuilderInterface {
        return $this->formFactory->createNamedBuilder(name: $name, data: $data, options: $options);
    }

    #[Route(
        path: '/rz-admin/nodes',
        name: 'nodesHomePage',
    )]
    #[Route(
        path: '/rz-admin/nodes/drafts',
        name: 'nodesHomeDraftPage',
        defaults: ['filter' => 'draft'],
    )]
    #[Route(
        path: '/rz-admin/nodes/pending',
        name: 'nodesHomePendingPage',
        defaults: ['filter' => 'pending'],
    )]
    #[Route(
        path: '/rz-admin/nodes/archived',
        name: 'nodesHomeArchivedPage',
        defaults: ['filter' => 'archived'],
    )]
    #[Route(
        path: '/rz-admin/nodes/deleted',
        name: 'nodesHomeDeletedPage',
        defaults: ['filter' => 'deleted'],
    )]
    #[Route(
        path: '/rz-admin/nodes/shadow',
        name: 'nodesHomeShadowPage',
        defaults: ['filter' => 'shadow'],
    )]
    public function indexAction(Request $request, ?string $filter = null): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODES');
        $translation = $this->getDefaultTranslation();

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

        /*
         * Handle bulk undelete form
         */
        if ($this->isGranted('ROLE_ACCESS_NODES_DELETE')) {
            $undeleteNodesForm = $this->buildBulkUndeleteForm();
            $assignation['undeleteNodesForm'] = $undeleteNodesForm->createView();
        }

        $assignation['filters'] = $listManager->getAssignation();
        $assignation['translation'] = $translation;
        $assignation['availableTranslations'] = $this->translationRepository->findAll();
        $assignation['nodes'] = $listManager->getEntities();
        $assignation['nodeTypes'] = $this->nodeTypesBag->allVisible();

        return $this->render('@RoadizRozier/nodes/list.html.twig', $assignation);
    }

    #[Route(
        path: '/rz-admin/nodes/edit/{nodeId}',
        name: 'nodesEditPage',
        requirements: ['nodeId' => '[0-9]+'],
    )]
    public function editAction(
        Request $request,
        #[MapEntity(
            expr: 'repository.find(nodeId)',
            evictCache: true,
            message: 'Node does not exist'
        )]
        Node $node,
    ): Response {
        $assignation = [];
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

        $translation = $this->getDefaultTranslation();
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

    #[Route(
        path: '/rz-admin/nodes/edit/{nodeId}/stacktype/{typeName}/remove',
        name: 'nodesRemoveStackTypePage',
        requirements: [
            'nodeId' => '[0-9]+',
            'typeName' => '[a-zA-Z]+',
        ],
    )]
    public function removeStackTypeAction(
        Request $request,
        #[MapEntity(
            expr: 'repository.find(nodeId)',
            message: 'Node does not exist'
        )]
        Node $node,
        string $typeName,
    ): Response {
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

    #[Route(
        path: '/rz-admin/nodes/add/{translationId}',
        name: 'nodesAddPage',
        requirements: ['translationId' => '[0-9]+'],
    )]
    public function addAction(
        Request $request,
        #[MapEntity(
            expr: 'repository.find(translationId)',
            message: 'Translation does not exist'
        )]
        Translation $translation,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODES');

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

    #[Route(
        path: '/rz-admin/nodes/add-child/{nodeId}',
        name: 'nodesAddChildPage',
        requirements: ['nodeId' => '[0-9]+'],
        defaults: ['nodeId' => null],
    )]
    public function addChildAction(
        Request $request,
        #[MapEntity(
            expr: 'repository.find(nodeId)',
            message: 'Parent node does not exist'
        )]
        ?Node $parentNode = null,
    ): Response {
        /** @var TranslationInterface $translation */
        $translation = $this->getDefaultTranslation();
        $nodeTypesCount = $this->nodeTypesBag->count();

        if (null === $translation) {
            throw new ResourceNotFoundException('Translation does not exist');
        }

        if (null === $parentNode) {
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

    #[Route(
        path: '/rz-admin/nodes/delete/{nodeId}',
        name: 'nodesDeletePage',
        requirements: ['nodeId' => '[0-9]+'],
        defaults: ['nodeId' => null],
    )]
    public function deleteAction(
        Request $request,
        #[MapEntity(
            expr: 'repository.find(nodeId)',
            message: 'Node does not exist'
        )]
        Node $node,
    ): Response {
        $this->denyAccessUnlessGranted(NodeVoter::DELETE, $node);

        $workflow = $this->workflowRegistry->get($node);
        if (!$workflow->can($node, 'delete')) {
            $this->logTrail->publishErrorMessage($request, sprintf('Node #%s cannot be deleted.', $node->getId()), $node);

            return $this->redirectToRoute(
                'nodesEditSourcePage',
                [
                    'nodeId' => $node->getId(),
                    'translationId' => $this->getDefaultTranslation()->getId(),
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
                        $breadcrumbsItem->url ?? throw new UnprocessableEntityHttpException('Cannot determine parent node URL.')
                    );
                }

                return $this->redirectToRoute(
                    'nodesEditSourcePage',
                    [
                        'nodeId' => $parent->getId(),
                        'translationId' => $this->getDefaultTranslation()->getId(),
                    ]
                );
            }

            return $this->redirectToRoute('nodesHomePage');
        }

        $title = $this->translator->trans(
            'delete.node.%name%',
            ['%name%' => $node->getNodeName()]
        );

        return $this->render('@RoadizRozier/admin/confirm_action.html.twig', [
            'title' => $title,
            'headPath' => '@RoadizRozier/nodes/head.html.twig',
            'cancelPath' => $this->generateUrl('nodesEditPage', ['nodeId' => $node->getId()]),
            'alertMessage' => 'are_you_sure.delete.node.and.data',
            'form' => $form->createView(),
        ]);
    }

    #[Route(
        path: '/rz-admin/nodes/empty-trash',
        name: 'nodesEmptyTrashPage',
    )]
    public function emptyTrashAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted(NodeVoter::EMPTY_TRASH);

        $form = $this->buildEmptyTrashForm();
        $form->handleRequest($request);

        $criteria = ['status' => NodeStatus::DELETED];
        /** @var Node|null $chroot */
        $chroot = $this->nodeChrootResolver->getChroot($this->getUser());
        if (null !== $chroot) {
            $criteria['parent'] = $this->nodeOffspringResolver->getAllOffspringIds($chroot);
        }

        $nodes = $this->allStatusesNodeRepository->findBy($criteria);

        if ($form->isSubmitted() && $form->isValid()) {
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

        $items = [];
        foreach ($nodes as $node) {
            $items[] = $this->explorerItemFactory->createForEntity($node)->toArray();
        }

        return $this->render('@RoadizRozier/admin/confirm_action.html.twig', [
            'title' => $this->translator->trans('empty.node.trash'),
            'headPath' => '@RoadizRozier/nodes/head.html.twig',
            'messageType' => 'neutral',
            'action_icon' => 'rz-icon-ri--delete-bin-7-line',
            'action_color' => 'danger',
            'action_label' => 'empty.trash',
            'cancelPath' => $this->generateUrl('nodesHomeDeletedPage'),
            'alertMessage' => 'are_you_sure.empty.node.and.data.trash',
            'form' => $form->createView(),
            'items' => $items,
        ]);
    }

    #[Route(
        path: '/rz-admin/nodes/undelete/{nodeId}',
        name: 'nodesUndeletePage',
        requirements: ['nodeId' => '[0-9]+'],
        defaults: ['nodeId' => null],
    )]
    public function undeleteAction(
        Request $request,
        #[MapEntity(
            expr: 'repository.find(nodeId)',
            message: 'Node does not exist'
        )]
        Node $node,
    ): Response {
        $this->denyAccessUnlessGranted(NodeVoter::DELETE, $node);

        $workflow = $this->workflowRegistry->get($node);
        if (!$workflow->can($node, 'undelete')) {
            $this->logTrail->publishErrorMessage($request, sprintf('Node #%s cannot be undeleted.', $node->getId()), $node);

            return $this->redirectToRoute(
                'nodesEditSourcePage',
                [
                    'nodeId' => $node->getId(),
                    'translationId' => $this->getDefaultTranslation()->getId(),
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

    #[Route(
        path: '/rz-admin/nodes/create',
        name: 'nodesGenerateAndAddNodeAction',
        methods: ['POST'],
        format: 'json',
    )]
    public function generateAndAddNodeAction(
        Request $request,
        #[MapRequestPayload]
        NodeCreationDto $nodeCreationDto,
    ): Response {
        // Authorization is checked within UniqueNodeGenerator::generateFromDto, so no need to check here.
        if (!$this->isCsrfTokenValid(AbstractAjaxController::AJAX_TOKEN_INTENTION, $nodeCreationDto->csrfToken)) {
            throw new BadRequestHttpException('Bad CSRF token');
        }

        try {
            $source = $this->uniqueNodeGenerator->generateFromDto($nodeCreationDto);

            $this->eventDispatcher->dispatch(new NodeCreatedEvent($source->getNode()));

            $msg = $this->translator->trans(
                'added.node.%name%',
                [
                    '%name%' => $source->getTitle(),
                ]
            );
            $this->logTrail->publishConfirmMessage($request, $msg, $source);

            return new JsonResponse(
                [
                    'statusCode' => Response::HTTP_CREATED,
                    'status' => 'success',
                    'responseText' => $msg,
                ],
                Response::HTTP_CREATED,
            );
        } catch (\Exception $e) {
            $msg = $this->translator->trans($e->getMessage());
            $this->logTrail->publishErrorMessage($request, $msg);
            throw new UnprocessableEntityHttpException($msg);
        }
    }

    #[Route(
        path: '/rz-admin/nodes/publish-all/{nodeId}',
        name: 'nodesPublishAllAction',
        requirements: ['nodeId' => '[0-9]+'],
    )]
    public function publishAllAction(
        Request $request,
        #[MapEntity(
            expr: 'repository.find(nodeId)',
            message: 'Node does not exist'
        )]
        Node $node,
    ): Response {
        $this->denyAccessUnlessGranted(NodeVoter::EDIT_STATUS, $node);

        $workflow = $this->workflowRegistry->get($node);
        if (!$workflow->can($node, 'publish')) {
            $this->logTrail->publishErrorMessage($request, sprintf('Node #%s cannot be published.', $node->getId()), $node);

            return $this->redirectToRoute(
                'nodesEditSourcePage',
                [
                    'nodeId' => $node->getId(),
                    'translationId' => $this->getDefaultTranslation()->getId(),
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
                'nodeId' => $node->getId(),
                'translationId' => $node->getNodeSources()->first() ?
                    $node->getNodeSources()->first()->getTranslation()->getId() :
                    null,
            ]);
        }

        $title = $this->translator->trans(
            'publish.node.%name%.offspring',
            ['%name%' => $node->getNodeName()]
        );

        return $this->render('@RoadizRozier/admin/confirm_action.html.twig', [
            'title' => $title,
            'headPath' => '@RoadizRozier/nodes/head.html.twig',
            'messageType' => 'neutral',
            'action_icon' => 'rz-icon-ri--check-line',
            'action_color' => 'success',
            'action_label' => 'publish-all',
            'cancelPath' => $this->generateUrl('nodesEditPage', ['nodeId' => $node->getId()]),
            'alertMessage' => 'are_you_sure.publish.node.offspring',
            'form' => $form->createView(),
            'items' => [], // TODO: add children node_sources list
        ]);
    }

    protected function getDefaultTranslation(): TranslationInterface
    {
        return $this->translationRepository->findDefault() ?? throw new ResourceNotFoundException('Default translation does not exist');
    }
}
