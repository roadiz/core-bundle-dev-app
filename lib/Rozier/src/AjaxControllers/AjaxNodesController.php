<?php

declare(strict_types=1);

namespace Themes\Rozier\AjaxControllers;

use Psr\Log\LoggerInterface;
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
use RZ\Roadiz\CoreBundle\Security\Authorization\Chroot\NodeChrootResolver;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Workflow\Registry;

class AjaxNodesController extends AbstractAjaxController
{
    public function __construct(
        private readonly NodeNamePolicyInterface $nodeNamePolicy,
        private readonly LoggerInterface $logger,
        private readonly NodeMover $nodeMover,
        private readonly NodeChrootResolver $nodeChrootResolver,
        private readonly Registry $workflowRegistry,
        private readonly UniqueNodeGenerator $uniqueNodeGenerator
    ) {
    }

    /**
     * @param  Request $request
     * @param  int $nodeId
     * @return JsonResponse
     */
    public function getTagsAction(Request $request, int $nodeId): JsonResponse
    {
        $tags = [];
        /** @var Node|null $node */
        $node = $this->em()->find(Node::class, (int) $nodeId);
        if (null === $node) {
            throw new NotFoundHttpException('Node not found');
        }

        $this->denyAccessUnlessGranted(NodeVoter::READ, $node);

        /** @var Tag $tag */
        foreach ($node->getTags() as $tag) {
            $tags[] = $tag->getFullPath();
        }

        return new JsonResponse(
            $tags
        );
    }

    /**
     * Handle AJAX edition requests for Node
     * such as coming from node-tree widgets.
     *
     * @param Request $request
     * @param int|string $nodeId
     *
     * @return Response JSON response
     */
    public function editAction(Request $request, int|string $nodeId): Response
    {
        $this->validateRequest($request);

        /** @var Node|null $node */
        $node = $this->em()->find(Node::class, (int) $nodeId);

        if (null === $node) {
            throw $this->createNotFoundException($this->getTranslator()->trans('node.%nodeId%.not_exists', [
                '%nodeId%' => $nodeId,
            ]));
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
                    'responseText' => $this->getTranslator()->trans('node.%name%.was_moved', [
                        '%name%' => $node->getNodeName(),
                    ]),
                ];
                break;
            case 'duplicate':
                $this->denyAccessUnlessGranted(NodeVoter::DUPLICATE, $node);
                $duplicator = new NodeDuplicator(
                    $node,
                    $this->em(),
                    $this->nodeNamePolicy
                );
                $newNode = $duplicator->duplicate();
                /*
                 * Dispatch event
                 */
                $this->dispatchEvent(new NodeCreatedEvent($newNode));
                $this->dispatchEvent(new NodeDuplicatedEvent($newNode));

                $msg = $this->getTranslator()->trans('duplicated.node.%name%', [
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

    /**
     * @param array $parameters
     * @param Node  $node
     */
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
            if ($node->getNodeType()->isReachable()) {
                $oldPaths = $this->nodeMover->getNodeSourcesUrls($node);
            }
        } catch (SameNodeUrlException $e) {
            $oldPaths = [];
        }

        $this->nodeMover->move($node, $parent, $position);
        $this->em()->flush();
        /*
         * Dispatch event
         */
        if (isset($oldPaths) && count($oldPaths) > 0 && !$node->isHome()) {
            $this->logger->debug('NodesSources paths changed', ['paths' => $oldPaths]);
            $this->dispatchEvent(new NodePathChangedEvent($node, $oldPaths));
        } else {
            $this->logger->debug('NodesSources paths did not change');
        }
        $this->dispatchEvent(new NodeUpdatedEvent($node));

        foreach ($node->getNodeSources() as $nodeSource) {
            $this->dispatchEvent(new NodesSourcesUpdatedEvent($nodeSource));
        }

        $msg = $this->getTranslator()->trans('node.%name%.was_moved', [
            '%name%' => $node->getNodeName(),
        ]);
        $this->logger->info($msg, ['entity' => $node->getNodeSources()->first() ?: $node]);

        $this->em()->flush();
    }

    /**
     * @param array     $parameters
     *
     * @return Node|null
     */
    protected function parseParentNode(array $parameters): ?Node
    {
        if (!empty($parameters['newParent']) && $parameters['newParent'] > 0) {
            return $this->em()->find(Node::class, (int) $parameters['newParent']);
        } elseif (null !== $this->getUser()) {
            // If user is jailed in a node, prevent moving nodes out.
            return $this->nodeChrootResolver->getChroot($this->getUser());
        }
        return null;
    }

    /**
     * @param array $parameters
     * @param float $default
     *
     * @return float
     */
    protected function parsePosition(array $parameters, float $default = 0.0): float
    {
        if (key_exists('nextNodeId', $parameters) && (int) $parameters['nextNodeId'] > 0) {
            /** @var Node $nextNode */
            $nextNode = $this->em()->find(Node::class, (int) $parameters['nextNodeId']);
            if ($nextNode !== null) {
                return $nextNode->getPosition() - 0.5;
            }
        } elseif (key_exists('prevNodeId', $parameters) && $parameters['prevNodeId'] > 0) {
            /** @var Node $prevNode */
            $prevNode = $this->em()->find(Node::class, (int) $parameters['prevNodeId']);
            if ($prevNode !== null) {
                return $prevNode->getPosition() + 0.5;
            }
        } elseif (key_exists('firstPosition', $parameters) && (bool) $parameters['firstPosition'] === true) {
            return -0.5;
        } elseif (key_exists('lastPosition', $parameters) && (bool) $parameters['lastPosition'] === true) {
            return 99999999;
        }
        return $default;
    }

    /**
     * Update node's status.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function statusesAction(Request $request): JsonResponse
    {
        $this->validateRequest($request);

        if ($request->get('nodeId', 0) <= 0) {
            throw new BadRequestHttpException($this->getTranslator()->trans('node.id.not_specified'));
        }

        /** @var Node|null $node */
        $node = $this->em()->find(Node::class, (int) $request->get('nodeId'));
        if (null === $node) {
            throw $this->createNotFoundException($this->getTranslator()->trans('node.%nodeId%.not_exists', [
                '%nodeId%' => $request->get('nodeId'),
            ]));
        }

        $this->denyAccessUnlessGranted(NodeVoter::EDIT_STATUS, $node);

        $availableStatuses = [
            'visible' => 'setVisible',
            'locked' => 'setLocked',
            'hideChildren' => 'setHidingChildren',
            'sterile' => 'setSterile',
        ];

        if ("nodeChangeStatus" == $request->get('_action') && "" != $request->get('statusName')) {
            if ($request->get('statusName') === 'status') {
                return $this->changeNodeStatus($node, $request->get('statusValue'));
            }

            /*
             * Check if status name is a valid boolean node field.
             */
            if (in_array($request->get('statusName'), array_keys($availableStatuses))) {
                $setter = $availableStatuses[$request->get('statusName')];
                $value = $request->get('statusValue');
                $node->$setter((bool) $value);

                /*
                 * If set locked to true,
                 * need to disable dynamic nodeName
                 */
                if ($request->get('statusName') == 'locked' && $value === true) {
                    $node->setDynamicNodeName(false);
                }

                $this->em()->flush();

                /*
                 * Dispatch event
                 */
                if ($request->get('statusName') === 'visible') {
                    $msg = $this->getTranslator()->trans('node.%name%.visibility_changed_to.%visible%', [
                        '%name%' => $node->getNodeName(),
                        '%visible%' => $node->isVisible() ? $this->getTranslator()->trans('visible') : $this->getTranslator()->trans('invisible'),
                    ]);
                    $this->publishConfirmMessage($request, $msg, $node->getNodeSources()->first() ?: $node);
                    $this->dispatchEvent(new NodeVisibilityChangedEvent($node));
                } else {
                    $msg = $this->getTranslator()->trans('node.%name%.%field%.updated', [
                        '%name%' => $node->getNodeName(),
                        '%field%' => $request->get('statusName'),
                    ]);
                    $this->publishConfirmMessage($request, $msg, $node->getNodeSources()->first() ?: $node);
                }
                $this->dispatchEvent(new NodeUpdatedEvent($node));
                $this->em()->flush();

                $responseArray = [
                    'statusCode' => Response::HTTP_PARTIAL_CONTENT,
                    'status' => 'success',
                    'responseText' => $msg,
                    'name' => $request->get('statusName'),
                    'value' => $value,
                ];
            } else {
                throw new BadRequestHttpException($this->getTranslator()->trans('node.has_no.field.%field%', [
                    '%field%' => $request->get('statusName'),
                ]));
            }
        } else {
            throw new BadRequestHttpException('Status field name is invalid.');
        }

        return new JsonResponse(
            $responseArray,
            $responseArray['statusCode']
        );
    }

    /**
     * @param Node   $node
     * @param string $transition
     *
     * @return JsonResponse
     */
    protected function changeNodeStatus(Node $node, string $transition): JsonResponse
    {
        $request = $this->getRequest();
        $workflow = $this->workflowRegistry->get($node);

        $workflow->apply($node, $transition);
        $this->em()->flush();
        $msg = $this->getTranslator()->trans('node.%name%.status_changed_to.%status%', [
            '%name%' => $node->getNodeName(),
            '%status%' => $this->getTranslator()->trans(Node::getStatusLabel($node->getStatus())),
        ]);
        $this->publishConfirmMessage($request, $msg, $node->getNodeSources()->first() ?: $node);

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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function quickAddAction(Request $request): JsonResponse
    {
        /*
         * Validate
         */
        $this->validateRequest($request);

        try {
            // Access security is handled by UniqueNodeGenerator
            $source = $this->uniqueNodeGenerator->generateFromRequest($request);

            /*
             * Dispatch event
             */
            $this->dispatchEvent(new NodeCreatedEvent($source->getNode()));

            $msg = $this->getTranslator()->trans(
                'added.node.%name%',
                [
                    '%name%' => $source->getTitle(),
                ]
            );
            $this->publishConfirmMessage($request, $msg, $source);

            $responseArray = [
                'statusCode' => Response::HTTP_CREATED,
                'status' => 'success',
                'responseText' => $msg,
            ];
        } catch (\Exception $e) {
            $msg = $this->getTranslator()->trans($e->getMessage());
            $this->logger->error($msg);
            throw new BadRequestHttpException($msg);
        }

        return new JsonResponse(
            $responseArray,
            $responseArray['statusCode']
        );
    }
}
