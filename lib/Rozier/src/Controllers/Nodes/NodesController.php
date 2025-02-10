<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\Nodes;

use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
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
use RZ\Roadiz\CoreBundle\ListManager\SessionListFilters;
use RZ\Roadiz\CoreBundle\Node\Exception\SameNodeUrlException;
use RZ\Roadiz\CoreBundle\Node\NodeFactory;
use RZ\Roadiz\CoreBundle\Node\NodeMover;
use RZ\Roadiz\CoreBundle\Node\NodeOffspringResolverInterface;
use RZ\Roadiz\CoreBundle\Node\UniqueNodeGenerator;
use RZ\Roadiz\CoreBundle\Security\Authorization\Chroot\NodeChrootResolver;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Workflow\Registry;
use Themes\Rozier\RozierApp;
use Themes\Rozier\Traits\NodesTrait;
use Twig\Error\RuntimeError;

final class NodesController extends RozierApp
{
    use NodesTrait;

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
        private readonly NodeTypes $nodeTypesBag,
        private readonly string $nodeFormTypeClass,
        private readonly string $addNodeFormTypeClass,
    ) {
    }

    protected function getNodeFactory(): NodeFactory
    {
        return $this->nodeFactory;
    }

    /**
     * List every node.
     */
    public function indexAction(Request $request, ?string $filter = null): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODES');
        $translation = $this->em()->getRepository(Translation::class)->findDefault();

        /** @var User|null $user */
        $user = $this->getUser();

        switch ($filter) {
            case 'draft':
                $this->assignation['mainFilter'] = $filter;
                $arrayFilter = [
                    'status' => NodeStatus::DRAFT,
                ];
                break;
            case 'pending':
                $this->assignation['mainFilter'] = $filter;
                $arrayFilter = [
                    'status' => NodeStatus::PENDING,
                ];
                break;
            case 'archived':
                $this->assignation['mainFilter'] = $filter;
                $arrayFilter = [
                    'status' => NodeStatus::ARCHIVED,
                ];
                break;
            case 'deleted':
                $this->assignation['mainFilter'] = $filter;
                $arrayFilter = [
                    'status' => NodeStatus::DELETED,
                ];
                break;
            default:
                $this->assignation['mainFilter'] = 'all';
                $arrayFilter = [];
                break;
        }

        if (null !== $user) {
            $arrayFilter['chroot'] = $this->nodeChrootResolver->getChroot($user);
        }

        /*
         * Manage get request to filter list
         */
        $listManager = $this->createEntityListManager(
            Node::class,
            $arrayFilter
        );
        $listManager->setDisplayingNotPublishedNodes(true);
        $listManager->setDisplayingAllNodesStatuses(true);

        /*
         * Stored in session
         */
        $sessionListFilter = new SessionListFilters('node_list_item_per_page');
        $sessionListFilter->handleItemPerPage($request, $listManager);
        $listManager->handle();

        $this->assignation['filters'] = $listManager->getAssignation();
        $this->assignation['translation'] = $translation;
        $this->assignation['availableTranslations'] = $this->em()
            ->getRepository(Translation::class)
            ->findAll();
        $this->assignation['nodes'] = $listManager->getEntities();
        $this->assignation['nodeTypes'] = $this->nodeTypesBag->allVisible();

        return $this->render('@RoadizRozier/nodes/list.html.twig', $this->assignation);
    }

    /**
     * Return an edition form for requested node.
     *
     * @throws RuntimeError
     */
    public function editAction(Request $request, int $nodeId, ?int $translationId = null): Response
    {
        /** @var Node|null $node */
        $node = $this->em()->find(Node::class, $nodeId);
        if (null === $node) {
            throw new ResourceNotFoundException(sprintf('Node #%s does not exist.', $nodeId));
        }

        $this->denyAccessUnlessGranted(NodeVoter::EDIT_SETTING, $node);

        $this->em()->refresh($node);
        /*
         * Handle StackTypes form
         */
        $stackTypesForm = $this->buildStackTypesForm($node);
        if (null !== $stackTypesForm) {
            $stackTypesForm->handleRequest($request);
            if ($stackTypesForm->isSubmitted() && $stackTypesForm->isValid()) {
                try {
                    $type = $this->addStackType($stackTypesForm->getData(), $node, $this->nodeTypesBag);
                    $msg = $this->getTranslator()->trans(
                        'stack_node.%name%.has_new_type.%type%',
                        [
                            '%name%' => $node->getNodeName(),
                            '%type%' => $type->getDisplayName(),
                        ]
                    );
                    $this->publishConfirmMessage($request, $msg, $node);

                    return $this->redirectToRoute(
                        'nodesEditPage',
                        ['nodeId' => $node->getId()]
                    );
                } catch (EntityAlreadyExistsException $e) {
                    $stackTypesForm->addError(new FormError($e->getMessage()));
                }
            }
            $this->assignation['stackTypesForm'] = $stackTypesForm->createView();
        }

        /*
         * Handle main form
         */
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
                $this->em()->flush();
                /*
                 * Dispatch event
                 */
                if (isset($oldPaths) && count($oldPaths) > 0 && !$node->isHome()) {
                    $this->dispatchEvent(new NodePathChangedEvent($node, $oldPaths));
                }
                $this->dispatchEvent(new NodeUpdatedEvent($node));
                $this->em()->flush();
                $msg = $this->getTranslator()->trans('node.%name%.updated', [
                    '%name%' => $node->getNodeName(),
                ]);
                $this->publishConfirmMessage($request, $msg, $node->getNodeSources()->first() ?: $node);

                return $this->redirectToRoute(
                    'nodesEditPage',
                    ['nodeId' => $node->getId()]
                );
            } catch (EntityAlreadyExistsException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        $translation = $this->em()->getRepository(Translation::class)->findDefault();
        $source = $node->getNodeSourcesByTranslation($translation)->first() ?: null;

        if (null === $source) {
            $availableTranslations = $this->em()
                ->getRepository(Translation::class)
                ->findAvailableTranslationsForNode($node);
            $this->assignation['available_translations'] = $availableTranslations;
        }
        $this->assignation['node'] = $node;
        $this->assignation['source'] = $source;
        $this->assignation['translation'] = $translation;
        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/nodes/edit.html.twig', $this->assignation);
    }

    public function removeStackTypeAction(Request $request, int $nodeId, string $typeName): Response
    {
        /** @var Node|null $node */
        $node = $this->em()->find(Node::class, $nodeId);
        if (null === $node) {
            throw new ResourceNotFoundException(sprintf('Node #%s does not exist.', $nodeId));
        }
        $this->denyAccessUnlessGranted(NodeVoter::EDIT_SETTING, $node);

        $type = $this->nodeTypesBag->get($typeName);
        if (null === $type) {
            throw new ResourceNotFoundException(sprintf('NodeType #%s does not exist.', $typeName));
        }

        $node->removeStackType($type);
        $this->em()->flush();

        $msg = $this->getTranslator()->trans(
            'stack_type.%type%.has_been_removed.%name%',
            [
                '%name%' => $node->getNodeName(),
                '%type%' => $type->getDisplayName(),
            ]
        );
        $this->publishConfirmMessage($request, $msg, $node->getNodeSources()->first() ?: null);

        return $this->redirectToRoute('nodesEditPage', ['nodeId' => $node->getId()]);
    }

    /**
     * Handle node creation pages.
     *
     * @throws RuntimeError
     */
    public function addAction(Request $request, ?int $translationId = null): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODES');

        /** @var Translation|null $translation */
        $translation = $this->em()->getRepository(Translation::class)->findDefault();

        if (null !== $translationId) {
            $translation = $this->em()->find(Translation::class, $translationId);
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
                $this->em()->refresh($node);
                /*
                 * Dispatch event
                 */
                $this->dispatchEvent(new NodeCreatedEvent($node));
                /** @var NodeType $nodeType */
                $nodeType = $form->get('nodeTypeName')->getData();
                if (null === $nodeType) {
                    throw new ResourceNotFoundException(sprintf('Node-type #%s does not exist.', $nodeType->getName()));
                }
                $node->setNodeTypeName($nodeType->getName());
                $node->setTtl($nodeType->getDefaultTtl());

                $msg = $this->getTranslator()->trans(
                    'node.%name%.created',
                    ['%name%' => $node->getNodeName()]
                );
                $this->publishConfirmMessage($request, $msg, $node->getNodeSources()->first() ?: null);

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

        $this->assignation['translation'] = $translation;
        $this->assignation['form'] = $form->createView();
        $this->assignation['nodeTypesCount'] = true;

        return $this->render('@RoadizRozier/nodes/add.html.twig', $this->assignation);
    }

    /**
     * Handle node creation pages.
     *
     * @throws RuntimeError
     */
    public function addChildAction(Request $request, ?int $nodeId = null, ?int $translationId = null): Response
    {
        /** @var Translation|null $translation */
        $translation = $this->em()->getRepository(Translation::class)->findDefault();

        $nodeTypesCount = $this->nodeTypesBag->count();

        if (null !== $translationId) {
            /** @var Translation|null $translation */
            $translation = $this->em()->find(Translation::class, $translationId);
        }

        if (null === $translation) {
            throw new ResourceNotFoundException('Translation does not exist');
        }

        if (null !== $nodeId && $nodeId > 0) {
            /** @var Node|null $parentNode */
            $parentNode = $this->em()->find(Node::class, $nodeId);
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
                $this->em()->refresh($node);

                /*
                 * Dispatch event
                 */
                $this->dispatchEvent(new NodeCreatedEvent($node));

                $msg = $this->getTranslator()->trans(
                    'child_node.%name%.created',
                    ['%name%' => $node->getNodeName()]
                );
                $this->publishConfirmMessage($request, $msg, $node->getNodeSources()->first() ?: null);

                return $this->redirectToRoute(
                    'nodesEditSourcePage',
                    [
                        'nodeId' => $node->getId(),
                        'translationId' => $translation->getId(),
                    ]
                );
            } catch (EntityAlreadyExistsException $e) {
                $form->addError(new FormError($e->getMessage()));
            } catch (\InvalidArgumentException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        $this->assignation['translation'] = $translation;
        $this->assignation['form'] = $form->createView();
        $this->assignation['parentNode'] = $parentNode;
        $this->assignation['nodeTypesCount'] = $nodeTypesCount;

        return $this->render('@RoadizRozier/nodes/add.html.twig', $this->assignation);
    }

    /**
     * Return a deletion form for requested node.
     *
     * @throws RuntimeError
     */
    public function deleteAction(Request $request, int $nodeId): Response
    {
        /** @var Node|null $node */
        $node = $this->em()->find(Node::class, $nodeId);

        if (null === $node) {
            throw new ResourceNotFoundException(sprintf('Node #%s does not exist.', $nodeId));
        }

        $this->denyAccessUnlessGranted(NodeVoter::DELETE, $node);

        $workflow = $this->workflowRegistry->get($node);
        if (!$workflow->can($node, 'delete')) {
            $this->publishErrorMessage($request, sprintf('Node #%s cannot be deleted.', $nodeId), $node);

            return $this->redirectToRoute(
                'nodesEditSourcePage',
                [
                    'nodeId' => $node->getId(),
                    'translationId' => $this->em()->getRepository(Translation::class)->findDefault()->getId(),
                ]
            );
        }

        $this->assignation['node'] = $node;
        $form = $this->buildDeleteForm($node);
        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
            && $form->getData()['nodeId'] == $node->getId()
        ) {
            /** @var Node|null $parent */
            $parent = $node->getParent();
            /*
             * Dispatch event
             */
            $this->dispatchEvent(new NodeDeletedEvent($node));

            /** @var NodeHandler $nodeHandler */
            $nodeHandler = $this->handlerFactory->getHandler($node);
            $nodeHandler->softRemoveWithChildren();
            $this->em()->flush();

            $msg = $this->getTranslator()->trans(
                'node.%name%.deleted',
                ['%name%' => $node->getNodeName()]
            );
            $this->publishConfirmMessage($request, $msg, $node->getNodeSources()->first() ?: $node);

            $referrer = $request->query->get('referer');
            if (
                \is_string($referrer)
                && (new UnicodeString($referrer))->trim()->startsWith('/')
            ) {
                return $this->redirect($referrer);
            }
            if (null !== $parent) {
                return $this->redirectToRoute(
                    'nodesEditSourcePage',
                    [
                        'nodeId' => $parent->getId(),
                        'translationId' => $this->em()->getRepository(Translation::class)->findDefault()->getId(),
                    ]
                );
            }

            return $this->redirectToRoute('nodesHomePage');
        }
        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/nodes/delete.html.twig', $this->assignation);
    }

    /**
     * Empty trash action.
     *
     * @throws RuntimeError
     */
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

            $nodes = $this->em()
                ->getRepository(Node::class)
                ->setDisplayingAllNodesStatuses(true)
                ->setDisplayingNotPublishedNodes(true)
                ->findBy($criteria);

            /** @var Node $node */
            foreach ($nodes as $node) {
                /** @var NodeHandler $nodeHandler */
                $nodeHandler = $this->handlerFactory->getHandler($node);
                $nodeHandler->removeWithChildrenAndAssociations();
            }
            /*
             * Final flush
             */
            $this->em()->flush();

            $msg = $this->getTranslator()->trans('node.trash.emptied');
            $this->publishConfirmMessage($request, $msg);

            return $this->redirectToRoute('nodesHomeDeletedPage');
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/nodes/emptyTrash.html.twig', $this->assignation);
    }

    /**
     * Return an deletion form for requested node.
     *
     * @throws RuntimeError
     */
    public function undeleteAction(Request $request, int $nodeId): Response
    {
        /** @var Node|null $node */
        $node = $this->em()->find(Node::class, $nodeId);

        if (null === $node) {
            throw new ResourceNotFoundException(sprintf('Node #%s does not exist.', $nodeId));
        }

        $this->denyAccessUnlessGranted(NodeVoter::DELETE, $node);

        $workflow = $this->workflowRegistry->get($node);
        if (!$workflow->can($node, 'undelete')) {
            $this->publishErrorMessage($request, sprintf('Node #%s cannot be undeleted.', $nodeId), $node);

            return $this->redirectToRoute(
                'nodesEditSourcePage',
                [
                    'nodeId' => $node->getId(),
                    'translationId' => $this->em()->getRepository(Translation::class)->findDefault()->getId(),
                ]
            );
        }

        $this->assignation['node'] = $node;
        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->dispatchEvent(new NodeUndeletedEvent($node));

            /** @var NodeHandler $nodeHandler */
            $nodeHandler = $this->handlerFactory->getHandler($node);
            $nodeHandler->softUnremoveWithChildren();
            $this->em()->flush();

            $msg = $this->getTranslator()->trans(
                'node.%name%.undeleted',
                ['%name%' => $node->getNodeName()]
            );
            $this->publishConfirmMessage($request, $msg, $node->getNodeSources()->first() ?: $node);

            /*
             * Force redirect to avoid resending form when refreshing page
             */
            return $this->redirectToRoute('nodesEditPage', [
                'nodeId' => $node->getId(),
            ]);
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/nodes/undelete.html.twig', $this->assignation);
    }

    public function generateAndAddNodeAction(Request $request): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODES');

        try {
            $source = $this->uniqueNodeGenerator->generateFromRequest($request);
            /** @var Translation $translation */
            $translation = $source->getTranslation();
            /*
             * Dispatch event
             */
            $this->dispatchEvent(new NodeCreatedEvent($source->getNode()));

            return $this->redirectToRoute(
                'nodesEditSourcePage',
                [
                    'nodeId' => $source->getNode()->getId(),
                    'translationId' => $translation->getId(),
                ]
            );
        } catch (\Exception $e) {
            $msg = $this->getTranslator()->trans('node.noCreation.alreadyExists');
            throw new ResourceNotFoundException($msg);
        }
    }

    /**
     * @throws RuntimeError
     */
    public function publishAllAction(Request $request, int $nodeId): Response
    {
        /** @var Node|null $node */
        $node = $this->em()->find(Node::class, $nodeId);

        if (null === $node) {
            throw new ResourceNotFoundException(sprintf('Node #%s does not exist.', $nodeId));
        }
        $this->denyAccessUnlessGranted(NodeVoter::EDIT_STATUS, $node);

        $workflow = $this->workflowRegistry->get($node);
        if (!$workflow->can($node, 'publish')) {
            $this->publishErrorMessage($request, sprintf('Node #%s cannot be published.', $nodeId), $node);

            return $this->redirectToRoute(
                'nodesEditSourcePage',
                [
                    'nodeId' => $node->getId(),
                    'translationId' => $this->em()->getRepository(Translation::class)->findDefault()->getId(),
                ]
            );
        }

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var NodeHandler $nodeHandler */
            $nodeHandler = $this->handlerFactory->getHandler($node);
            $nodeHandler->publishWithChildren();
            $this->em()->flush();

            $msg = $this->getTranslator()->trans('node.offspring.published');
            $this->publishConfirmMessage($request, $msg, $node);

            return $this->redirectToRoute('nodesEditSourcePage', [
                'nodeId' => $nodeId,
                'translationId' => $node->getNodeSources()->first() ?
                    $node->getNodeSources()->first()->getTranslation()->getId() :
                    null,
            ]);
        }

        $this->assignation['node'] = $node;
        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/nodes/publishAll.html.twig', $this->assignation);
    }
}
