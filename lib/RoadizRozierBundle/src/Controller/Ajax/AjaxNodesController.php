<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Node;
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
use RZ\Roadiz\CoreBundle\Repository\AllStatusesNodeRepository;
use RZ\Roadiz\CoreBundle\Security\Authorization\Chroot\NodeChrootResolver;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\RozierBundle\Model\NodeDuplicateDto;
use RZ\Roadiz\RozierBundle\Model\NodePasteDto;
use RZ\Roadiz\RozierBundle\Model\NodeStatusDto;
use RZ\Roadiz\RozierBundle\Model\PositionDto;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AjaxNodesController extends AbstractAjaxController
{
    use UpdatePositionTrait;

    public function __construct(
        private readonly NodeNamePolicyInterface $nodeNamePolicy,
        private readonly LoggerInterface $logger,
        private readonly NodeMover $nodeMover,
        private readonly NodeChrootResolver $nodeChrootResolver,
        private readonly Registry $workflowRegistry,
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

    #[Route(
        path: '/rz-admin/ajax/nodes/paste',
        name: 'nodesPasteAjax',
        methods: ['POST'],
        format: 'json'
    )]
    public function pasteAction(
        Request $request,
        #[MapRequestPayload]
        NodePasteDto $nodePasteDto,
    ): JsonResponse {
        $this->validateCsrfToken($nodePasteDto->csrfToken);

        $newPosition = 99999999;
        $parentNode = null;

        /** @var Node|null $node */
        $node = $this->allStatusesNodeRepository->find($nodePasteDto->nodeId);
        if (null === $node) {
            throw $this->createNotFoundException($this->translator->trans('node.%nodeId%.not_exists', ['%nodeId%' => $nodePasteDto->nodeId]));
        }

        if (null !== $nodePasteDto->parentNodeId) {
            /** @var Node|null $parentNode */
            $parentNode = $this->allStatusesNodeRepository->find($nodePasteDto->parentNodeId);
            if (null === $parentNode) {
                throw $this->createNotFoundException($this->translator->trans('node.%nodeId%.not_exists', ['%nodeId%' => $nodePasteDto->parentNodeId]));
            }
        }

        if (null !== $nodePasteDto->prevNodeId) {
            /** @var Node|null $parentNode */
            $previousNode = $this->allStatusesNodeRepository->find($nodePasteDto->prevNodeId);
            if (null === $previousNode) {
                throw $this->createNotFoundException($this->translator->trans('node.%nodeId%.not_exists', ['%nodeId%' => $nodePasteDto->prevNodeId]));
            }

            $parentNode = $previousNode->getParent();
            $newPosition = $previousNode->getPosition() + 0.5;
        }

        $this->denyAccessUnlessGranted(NodeVoter::DUPLICATE, $node);
        if (null === $parentNode) {
            $this->denyAccessUnlessGranted(NodeVoter::CREATE_AT_ROOT);
        } else {
            $this->denyAccessUnlessGranted(NodeVoter::CREATE, $parentNode);
        }

        $duplicator = new NodeDuplicator(
            $node,
            $this->managerRegistry->getManager(),
            $this->nodeNamePolicy
        );
        $newNode = $duplicator->duplicate();

        $this->nodeMover->move($newNode, $parentNode, $newPosition);
        $this->managerRegistry->getManager()->flush();

        $this->eventDispatcher->dispatch(new NodeCreatedEvent($newNode));
        $this->eventDispatcher->dispatch(new NodeDuplicatedEvent($newNode));

        $msg = $this->translator->trans('copied.node.%name%', [
            '%name%' => $newNode->getNodeName(),
        ]);

        $this->logTrail->publishConfirmMessage($request, $msg, $newNode->getNodeSources()->first() ?: $newNode);

        return new JsonResponse(
            [
                'statusCode' => '200',
                'status' => 'success',
                'responseText' => $msg,
            ],
            Response::HTTP_PARTIAL_CONTENT
        );
    }

    #[Route(
        path: '/rz-admin/ajax/nodes/duplicate',
        name: 'nodesDuplicateAjax',
        methods: ['POST'],
        format: 'json'
    )]
    public function duplicateAction(
        Request $request,
        #[MapRequestPayload]
        NodeDuplicateDto $nodeDuplicateDto,
    ): JsonResponse {
        $this->validateCsrfToken($nodeDuplicateDto->csrfToken);

        /** @var Node|null $node */
        $node = $this->allStatusesNodeRepository->find($nodeDuplicateDto->nodeId);
        if (null === $node) {
            throw $this->createNotFoundException($this->translator->trans('node.%nodeId%.not_exists', ['%nodeId%' => $nodeDuplicateDto->nodeId]));
        }

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

        $this->logTrail->publishConfirmMessage($request, $msg, $newNode->getNodeSources()->first() ?: $newNode);

        return new JsonResponse(
            [
                'statusCode' => '200',
                'status' => 'success',
                'responseText' => $msg,
            ],
            Response::HTTP_PARTIAL_CONTENT
        );
    }

    #[Route(
        path: '/rz-admin/ajax/nodes/position',
        name: 'nodesPositionAjax',
        methods: ['POST'],
        format: 'json'
    )]
    public function updatePositionAction(
        #[MapRequestPayload]
        PositionDto $nodePositionDto,
    ): JsonResponse {
        $this->validateCsrfToken($nodePositionDto->csrfToken);

        /** @var Node|null $node */
        $node = $this->allStatusesNodeRepository->find($nodePositionDto->id);
        if (null === $node) {
            throw $this->createNotFoundException($this->translator->trans('node.%nodeId%.not_exists', ['%nodeId%' => $nodePositionDto->id]));
        }

        $this->denyAccessUnlessGranted(NodeVoter::EDIT_SETTING, $node);

        $this->updateNodePositionAndParent($nodePositionDto, $node);

        return new JsonResponse(
            [
                'statusCode' => '200',
                'status' => 'success',
                'responseText' => $this->translator->trans('node.%name%.was_moved', [
                    '%name%' => $node->getNodeName(),
                ]),
            ],
            Response::HTTP_PARTIAL_CONTENT
        );
    }

    private function updateNodePositionAndParent(PositionDto $nodePositionDto, Node $node): void
    {
        if ($node->isLocked()) {
            throw new BadRequestHttpException('Locked node cannot be moved.');
        }
        /*
         * First, we set the new parent
         */
        $parent = $this->parseParentNode($nodePositionDto);
        /*
         * Then compute new position
         */
        $position = $this->parsePosition($nodePositionDto, $this->allStatusesNodeRepository) ?? $node->getPosition();

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

    private function parseParentNode(PositionDto $nodePositionDto): ?Node
    {
        if (null !== $nodePositionDto->newParentId && $nodePositionDto->newParentId > 0) {
            return $this->allStatusesNodeRepository->find($nodePositionDto->newParentId);
        } elseif (null !== $this->getUser()) {
            // If user is jailed in a node, prevent moving nodes out.
            return $this->nodeChrootResolver->getChroot($this->getUser());
        }

        return null;
    }

    #[Route(
        path: '/rz-admin/ajax/nodes/statuses',
        name: 'nodesStatusesAjax',
        methods: ['POST'],
        format: 'json'
    )]
    public function statusesAction(
        Request $request,
        #[MapRequestPayload]
        NodeStatusDto $nodeStatusDto,
    ): JsonResponse {
        $this->validateCsrfToken($nodeStatusDto->csrfToken);

        /** @var Node|null $node */
        $node = $this->allStatusesNodeRepository->find($nodeStatusDto->nodeId);
        if (null === $node) {
            throw $this->createNotFoundException($this->translator->trans('node.%nodeId%.not_exists', ['%nodeId%' => $nodeStatusDto->nodeId]));
        }

        $this->denyAccessUnlessGranted(NodeVoter::EDIT_STATUS, $node);

        $availableStatuses = [
            'visible' => 'setVisible',
            'locked' => 'setLocked',
            'hideChildren' => 'setHidingChildren',
        ];

        if ('status' === $nodeStatusDto->statusName && is_string($nodeStatusDto->statusValue)) {
            return $this->changeNodeStatus($request, $node, $nodeStatusDto->statusValue);
        }

        /*
         * Check if status name is a valid boolean node field.
         */
        if (!in_array($nodeStatusDto->statusName, array_keys($availableStatuses))) {
            throw new BadRequestHttpException($this->translator->trans('node.has_no.field.%field%', ['%field%' => $nodeStatusDto->statusName]));
        }

        $setter = $availableStatuses[$nodeStatusDto->statusName];
        $value = (bool) $nodeStatusDto->statusValue;
        $node->$setter($value);

        /*
         * If set locked to true,
         * need to disable dynamic nodeName
         */
        if ('locked' === $nodeStatusDto->statusName && true === $value) {
            $node->setDynamicNodeName(false);
        }

        $this->managerRegistry->getManager()->flush();

        if ('visible' === $nodeStatusDto->statusName) {
            $msg = $this->translator->trans('node.%name%.visibility_changed_to.%visible%', [
                '%name%' => $node->getNodeName(),
                '%visible%' => $node->isVisible() ? $this->translator->trans('visible') : $this->translator->trans('invisible'),
            ]);
            $this->logTrail->publishConfirmMessage($request, $msg, $node->getNodeSources()->first() ?: $node);
            $this->eventDispatcher->dispatch(new NodeVisibilityChangedEvent($node));
        } else {
            $msg = $this->translator->trans('node.%name%.%field%.updated', [
                '%name%' => $node->getNodeName(),
                '%field%' => $nodeStatusDto->statusName,
            ]);
            $this->logTrail->publishConfirmMessage($request, $msg, $node->getNodeSources()->first() ?: $node);
        }
        $this->eventDispatcher->dispatch(new NodeUpdatedEvent($node));
        $this->managerRegistry->getManager()->flush();

        $responseArray = [
            'statusCode' => Response::HTTP_PARTIAL_CONTENT,
            'status' => 'success',
            'responseText' => $msg,
            'name' => $nodeStatusDto->statusName,
            'value' => $value,
        ];

        return new JsonResponse(
            $responseArray,
            $responseArray['statusCode']
        );
    }

    private function changeNodeStatus(Request $request, Node $node, string $transition): JsonResponse
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
}
