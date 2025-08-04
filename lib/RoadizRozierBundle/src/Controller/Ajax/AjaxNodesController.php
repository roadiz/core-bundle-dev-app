<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Event\Node\NodeCreatedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeDuplicatedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodePathChangedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeUpdatedEvent;
use RZ\Roadiz\CoreBundle\Event\Node\NodeVisibilityChangedEvent;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesUpdatedEvent;
use RZ\Roadiz\CoreBundle\Node\Exception\SameNodeUrlException;
use RZ\Roadiz\CoreBundle\Node\NodeDuplicator;
use RZ\Roadiz\CoreBundle\Node\NodeMover;
use RZ\Roadiz\CoreBundle\Node\NodeNamePolicyInterface;
use RZ\Roadiz\CoreBundle\Node\UniqueNodeGenerator;
use RZ\Roadiz\CoreBundle\Repository\AllStatusesNodeRepository;
use RZ\Roadiz\CoreBundle\Security\Authorization\Chroot\NodeChrootResolver;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AjaxNodesController extends AbstractAjaxController
{
    public function __construct(
        private readonly NodeNamePolicyInterface $nodeNamePolicy,
        private readonly LoggerInterface $logger,
        private readonly NodeMover $nodeMover,
        private readonly NodeChrootResolver $nodeChrootResolver,
        private readonly Registry $workflowRegistry,
        private readonly UniqueNodeGenerator $uniqueNodeGenerator,
        private readonly NodeTypes $nodeTypesBag,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AllStatusesNodeRepository $allStatusesNodeRepository,
        private readonly LogTrail $logTrail,
        ManagerRegistry $managerRegistry,
        SerializerInterface $serializer,
        TranslatorInterface $translator,
    ) {
        parent::__construct($managerRegistry, $serializer, $translator);
    }

    public function getTagsAction(int $nodeId): JsonResponse
    {
        $tags = [];
        /** @var Node|null $node */
        $node = $this->allStatusesNodeRepository->find($nodeId);
        if (null === $node) {
            throw $this->createNotFoundException('Node not found');
        }

        $this->denyAccessUnlessGranted(NodeVoter::READ, $node);

        /** @var Tag $tag */
        foreach ($node->getTags() as $tag) {
            $tags[] = $tag->getFullPath();
        }

        return $this->createSerializedResponse(
            $tags
        );
    }

    /**
     * Handle AJAX edition requests for Node
     * such as coming from node-tree widgets.
     *
     * @return Response JSON response
     */
    public function editAction(Request $request, int|string $nodeId): Response
    {
        $this->validateRequest($request);

        /** @var Node|null $node */
        $node = $this->allStatusesNodeRepository->find((int) $nodeId);

        if (null === $node) {
            throw $this->createNotFoundException($this->translator->trans('node.%nodeId%.not_exists', ['%nodeId%' => $nodeId]));
        }
        /*
         * Get the right update method against "_action" parameter
         */
        switch ($request->get('_action')) {
            case 'updatePosition':
                $this->denyAccessUnlessGranted(NodeVoter::EDIT_SETTING, $node);
                $this->updatePosition($request->request->all(), $node);
                $responseArray = [
                    'statusCode' => '200',
                    'status' => 'success',
                    'responseText' => $this->translator->trans('node.%name%.was_moved', [
                        '%name%' => $node->getNodeName(),
                    ]),
                ];
                break;
            case 'duplicate':
                $this->denyAccessUnlessGranted(NodeVoter::DUPLICATE, $node);
                $duplicator = new NodeDuplicator(
                    $node,
                    $this->managerRegistry->getManager(),
                    $this->nodeNamePolicy
                );
                $newNode = $duplicator->duplicate();

                $this->eventDispatcher->dispatch(new NodeCreatedEvent($newNode));
                $this->eventDispatcher->dispatch(new NodeDuplicatedEvent($newNode));

                $msg = $this->translator->trans('duplicated.node.%name%', [
                    '%name%' => $node->getNodeName(),
                ]);
                $this->logger->info($msg, ['entity' => $newNode->getNodeSources()->first()]);

                $responseArray = [
                    'statusCode' => '200',
                    'status' => 'success',
                    'responseText' => $msg,
                ];
                break;
            default:
                throw new BadRequestHttpException('Action is not defined.');
        }

        return new JsonResponse(
            $responseArray,
            Response::HTTP_PARTIAL_CONTENT
        );
    }

    protected function updatePosition(array $parameters, Node $node): void
    {
        if ($node->isLocked()) {
            throw new BadRequestHttpException('Locked node cannot be moved.');
        }
        /*
         * First, we set the new parent
         */
        $parent = $this->parseParentNode($parameters);
        /*
         * Then compute new position
         */
        $position = $this->parsePosition($parameters, $node->getPosition());

        try {
            if ($this->nodeTypesBag->get($node->getNodeTypeName())?->isReachable()) {
                $oldPaths = $this->nodeMover->getNodeSourcesUrls($node);
            }
        } catch (SameNodeUrlException) {
            $oldPaths = [];
        }

        $this->nodeMover->move($node, $parent, $position);
        $this->managerRegistry->getManager()->flush();

        if (isset($oldPaths) && count($oldPaths) > 0 && !$node->isHome()) {
            $this->logger->debug('NodesSources paths changed', ['paths' => $oldPaths]);
            $this->eventDispatcher->dispatch(new NodePathChangedEvent($node, $oldPaths));
        } else {
            $this->logger->debug('NodesSources paths did not change');
        }
        $this->eventDispatcher->dispatch(new NodeUpdatedEvent($node));

        foreach ($node->getNodeSources() as $nodeSource) {
            $this->eventDispatcher->dispatch(new NodesSourcesUpdatedEvent($nodeSource));
        }

        $msg = $this->translator->trans('node.%name%.was_moved', [
            '%name%' => $node->getNodeName(),
        ]);
        $this->logger->info($msg, ['entity' => $node->getNodeSources()->first() ?: $node]);

        $this->managerRegistry->getManager()->flush();
    }

    protected function parseParentNode(array $parameters): ?Node
    {
        if (
            !empty($parameters['newParent'])
            && is_numeric($parameters['newParent'])
            && $parameters['newParent'] > 0
        ) {
            return $this->allStatusesNodeRepository->find((int) $parameters['newParent']);
        } elseif (null !== $this->getUser()) {
            // If user is jailed in a node, prevent moving nodes out.
            return $this->nodeChrootResolver->getChroot($this->getUser());
        }

        return null;
    }

    protected function parsePosition(array $parameters, float $default = 0.0): float
    {
        if (key_exists('nextNodeId', $parameters) && (int) $parameters['nextNodeId'] > 0) {
            /** @var Node $nextNode */
            $nextNode = $this->allStatusesNodeRepository->find((int) $parameters['nextNodeId']);
            if (null !== $nextNode) {
                return $nextNode->getPosition() - 0.5;
            }
        } elseif (key_exists('prevNodeId', $parameters) && $parameters['prevNodeId'] > 0) {
            /** @var Node $prevNode */
            $prevNode = $this->allStatusesNodeRepository->find((int) $parameters['prevNodeId']);
            if (null !== $prevNode) {
                return $prevNode->getPosition() + 0.5;
            }
        } elseif (key_exists('firstPosition', $parameters) && true === (bool) $parameters['firstPosition']) {
            return -0.5;
        } elseif (key_exists('lastPosition', $parameters) && true === (bool) $parameters['lastPosition']) {
            return 99999999;
        }

        return $default;
    }

    /**
     * Update node's status.
     */
    public function statusesAction(Request $request): JsonResponse
    {
        $this->validateRequest($request);

        if ($request->get('nodeId', 0) <= 0) {
            throw new BadRequestHttpException($this->translator->trans('node.id.not_specified'));
        }

        /** @var Node|null $node */
        $node = $this->allStatusesNodeRepository->find((int) $request->get('nodeId'));
        if (null === $node) {
            throw $this->createNotFoundException($this->translator->trans('node.%nodeId%.not_exists', ['%nodeId%' => $request->get('nodeId')]));
        }

        $this->denyAccessUnlessGranted(NodeVoter::EDIT_STATUS, $node);

        $availableStatuses = [
            'visible' => 'setVisible',
            'locked' => 'setLocked',
            'hideChildren' => 'setHidingChildren',
            'sterile' => 'setSterile',
        ];

        if ('nodeChangeStatus' !== $request->get('_action') || empty($request->get('statusName'))) {
            throw new BadRequestHttpException('Status field name is invalid.');
        }

        if ('status' === $request->get('statusName')) {
            return $this->changeNodeStatus($request, $node, $request->get('statusValue'));
        }

        /*
         * Check if status name is a valid boolean node field.
         */
        if (!in_array($request->get('statusName'), array_keys($availableStatuses))) {
            throw new BadRequestHttpException($this->translator->trans('node.has_no.field.%field%', ['%field%' => $request->get('statusName')]));
        }

        $setter = $availableStatuses[$request->get('statusName')];
        $value = $request->get('statusValue');
        $node->$setter((bool) $value);

        /*
         * If set locked to true,
         * need to disable dynamic nodeName
         */
        if ('locked' == $request->get('statusName') && true === $value) {
            $node->setDynamicNodeName(false);
        }

        $this->managerRegistry->getManager()->flush();

        if ('visible' === $request->get('statusName')) {
            $msg = $this->translator->trans('node.%name%.visibility_changed_to.%visible%', [
                '%name%' => $node->getNodeName(),
                '%visible%' => $node->isVisible() ? $this->translator->trans('visible') : $this->translator->trans('invisible'),
            ]);
            $this->logTrail->publishConfirmMessage($request, $msg, $node->getNodeSources()->first() ?: $node);
            $this->eventDispatcher->dispatch(new NodeVisibilityChangedEvent($node));
        } else {
            $msg = $this->translator->trans('node.%name%.%field%.updated', [
                '%name%' => $node->getNodeName(),
                '%field%' => $request->get('statusName'),
            ]);
            $this->logTrail->publishConfirmMessage($request, $msg, $node->getNodeSources()->first() ?: $node);
        }
        $this->eventDispatcher->dispatch(new NodeUpdatedEvent($node));
        $this->managerRegistry->getManager()->flush();

        $responseArray = [
            'statusCode' => Response::HTTP_PARTIAL_CONTENT,
            'status' => 'success',
            'responseText' => $msg,
            'name' => $request->get('statusName'),
            'value' => $value,
        ];

        return new JsonResponse(
            $responseArray,
            $responseArray['statusCode']
        );
    }

    protected function changeNodeStatus(Request $request, Node $node, string $transition): JsonResponse
    {
        $workflow = $this->workflowRegistry->get($node);

        $workflow->apply($node, $transition);
        $this->managerRegistry->getManager()->flush();
        $msg = $this->translator->trans('node.%name%.status_changed_to.%status%', [
            '%name%' => $node->getNodeName(),
            '%status%' => $node->getStatus()->trans($this->translator),
        ]);
        $this->logTrail->publishConfirmMessage($request, $msg, $node->getNodeSources()->first() ?: $node);

        return new JsonResponse(
            [
                'statusCode' => Response::HTTP_PARTIAL_CONTENT,
                'status' => 'success',
                'responseText' => $msg,
                'name' => 'status',
                'value' => $transition,
            ],
            Response::HTTP_PARTIAL_CONTENT
        );
    }

    public function quickAddAction(Request $request): JsonResponse
    {
        /*
         * Validate
         */
        $this->validateRequest($request);

        try {
            $source = $this->uniqueNodeGenerator->generateFromRequest($request);

            $this->eventDispatcher->dispatch(new NodeCreatedEvent($source->getNode()));

            $msg = $this->translator->trans(
                'added.node.%name%',
                [
                    '%name%' => $source->getTitle(),
                ]
            );
            $this->logTrail->publishConfirmMessage($request, $msg, $source);

            $responseArray = [
                'statusCode' => Response::HTTP_CREATED,
                'status' => 'success',
                'responseText' => $msg,
            ];
        } catch (\Exception $e) {
            $msg = $this->translator->trans($e->getMessage());
            $this->logger->error($msg);
            throw new BadRequestHttpException($msg);
        }

        return new JsonResponse(
            $responseArray,
            $responseArray['statusCode']
        );
    }
}
