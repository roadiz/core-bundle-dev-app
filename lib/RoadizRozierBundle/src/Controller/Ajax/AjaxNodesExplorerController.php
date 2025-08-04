<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Explorer\AbstractExplorerItem;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerItemFactoryInterface;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerFactoryInterface;
use RZ\Roadiz\CoreBundle\Repository\NotPublishedNodeRepository;
use RZ\Roadiz\CoreBundle\SearchEngine\NodeSourceSearchHandlerInterface;
use RZ\Roadiz\CoreBundle\SearchEngine\SearchResultItemInterface;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AjaxNodesExplorerController extends AbstractAjaxExplorerController
{
    public function __construct(
        private readonly ?NodeSourceSearchHandlerInterface $nodeSourceSearchHandler,
        private readonly NodeTypes $nodeTypesBag,
        private readonly NotPublishedNodeRepository $notPublishedNodeRepository,
        ExplorerItemFactoryInterface $explorerItemFactory,
        EventDispatcherInterface $eventDispatcher,
        EntityListManagerFactoryInterface $entityListManagerFactory,
        ManagerRegistry $managerRegistry,
        SerializerInterface $serializer,
        TranslatorInterface $translator,
    ) {
        parent::__construct(
            $explorerItemFactory,
            $eventDispatcher,
            $entityListManagerFactory,
            $managerRegistry,
            $serializer,
            $translator
        );
    }

    protected function getItemPerPage(): int
    {
        return 30;
    }

    protected function isSearchEngineAvailable(Request $request): bool
    {
        return null !== $this->nodeSourceSearchHandler && '' !== $request->get('search');
    }

    public function indexAction(Request $request): JsonResponse
    {
        // Only requires Search permission for nodes
        $this->denyAccessUnlessGranted(NodeVoter::SEARCH);

        $criteria = $this->parseFilterFromRequest($request);
        $sorting = $this->parseSortingFromRequest($request);
        if ($this->isSearchEngineAvailable($request)) {
            $responseArray = $this->getSolrSearchResults($request, $criteria);
        } else {
            $responseArray = $this->getNodeSearchResults($request, $criteria, $sorting);
        }

        if ($request->query->has('tagId') && $request->get('tagId') > 0) {
            $responseArray['filters'] = array_merge($responseArray['filters'], [
                'tagId' => $request->get('tagId'),
            ]);
        }

        return $this->createSerializedResponse($responseArray);
    }

    protected function parseFilterFromRequest(Request $request): array
    {
        $arrayFilter = [
            'status' => ['<=', NodeStatus::ARCHIVED],
        ];

        if ($request->query->has('tagId') && $request->get('tagId') > 0) {
            $tag = $this->managerRegistry->getRepository(Tag::class)->find($request->get('tagId'));

            $arrayFilter['tags'] = [$tag];
        }

        if ($request->query->has('nodeTypes')) {
            $nodeTypesRequest = $request->get('nodeTypes');
            if (\is_string($nodeTypesRequest) && '' !== trim($nodeTypesRequest)) {
                $nodeTypesRequest = array_filter(explode(',', $nodeTypesRequest));
            }
            if (\is_array($nodeTypesRequest) && count($nodeTypesRequest) > 0) {
                /** @var NodeType[] $nodeTypes */
                $nodeTypes = array_filter(array_map(fn (string $nodeTypeName) => $this->nodeTypesBag->get(trim($nodeTypeName)), $nodeTypesRequest));

                if (count($nodeTypes) > 0) {
                    $arrayFilter['nodeTypeName'] = array_map(fn (NodeType $nodeType) => $nodeType->getName(), $nodeTypes);
                }
            }
        }

        return $arrayFilter;
    }

    protected function parseSortingFromRequest(Request $request): array
    {
        if ($request->query->has('sort-alpha')) {
            return [
                'nodeName' => 'ASC',
            ];
        }

        return [
            'updatedAt' => 'DESC',
        ];
    }

    protected function getNodeSearchResults(Request $request, array $criteria, array $sorting = []): array
    {
        /*
         * Manage get request to filter list
         */
        $listManager = $this->createEntityListManager(
            Node::class,
            $criteria,
            $sorting
        );
        $listManager->setDisplayingNotPublishedNodes(true);
        $listManager->setItemPerPage($this->getItemPerPage());
        $listManager->handle();

        $nodes = $listManager->getEntities();
        $nodesArray = $this->normalizeNodes($nodes);

        return [
            'status' => 'confirm',
            'statusCode' => 200,
            'nodes' => $nodesArray,
            'nodesCount' => $listManager->getItemCount(),
            'filters' => $listManager->getAssignation(),
        ];
    }

    protected function getSolrSearchResults(
        Request $request,
        array $arrayFilter,
    ): array {
        $this->nodeSourceSearchHandler->boostByUpdateDate();
        $currentPage = $request->get('page', 1);
        $searchQuery = $request->get('search');

        if (!\is_string($searchQuery)) {
            throw new InvalidParameterException('Search query must be a string');
        }
        if (empty($searchQuery)) {
            throw new InvalidParameterException('Search query cannot be empty');
        }
        if ($currentPage < 1) {
            throw new InvalidParameterException('Current page must be greater than 0');
        }

        $results = $this->nodeSourceSearchHandler->search(
            $searchQuery,
            $arrayFilter,
            $this->getItemPerPage(),
            true,
            (int) $currentPage
        );
        $pageCount = ceil($results->getResultCount() / $this->getItemPerPage());
        $nodesArray = $this->normalizeNodes($results);

        return [
            'status' => 'confirm',
            'statusCode' => 200,
            'nodes' => $nodesArray,
            'nodesCount' => $results->getResultCount(),
            'filters' => [
                'currentPage' => $currentPage,
                'itemCount' => $results->getResultCount(),
                'itemPerPage' => $this->getItemPerPage(),
                'pageCount' => $pageCount,
                'nextPage' => $currentPage < $pageCount ? $currentPage + 1 : null,
            ],
        ];
    }

    /**
     * Get a Node list from an array of id.
     */
    public function listAction(Request $request): JsonResponse
    {
        // Only requires Search permission for nodes
        $this->denyAccessUnlessGranted(NodeVoter::SEARCH);

        $cleanNodeIds = array_filter($request->query->filter('ids', [], \FILTER_DEFAULT, [
            'flags' => \FILTER_FORCE_ARRAY,
        ]));
        $nodesArray = [];

        if (count($cleanNodeIds) > 0) {
            $nodes = $this->notPublishedNodeRepository
                ->findBy([
                    'id' => $cleanNodeIds,
                ]);

            // Sort array by ids given in request
            $nodes = $this->sortIsh($nodes, $cleanNodeIds);
            $nodesArray = $this->normalizeNodes($nodes);
        }

        return $this->createSerializedResponse([
            'status' => 'confirm',
            'statusCode' => 200,
            'items' => $nodesArray,
        ]);
    }

    /**
     * Normalize response Node list result.
     *
     * @param iterable<Node|NodesSources|SearchResultItemInterface> $nodes
     *
     * @return array<AbstractExplorerItem>
     */
    private function normalizeNodes(iterable $nodes): array
    {
        $nodesArray = [];

        foreach ($nodes as $node) {
            if ($node instanceof SearchResultItemInterface) {
                $item = $node->getItem();
                if ($item instanceof NodesSources || $item instanceof Node) {
                    $this->normalizeItem($item, $nodesArray);
                }
            } else {
                $this->normalizeItem($node, $nodesArray);
            }
        }

        return array_values($nodesArray);
    }

    private function normalizeItem(NodesSources|Node $item, array &$nodesArray): void
    {
        $model = $this->explorerItemFactory->createForEntity($item);
        if (!key_exists((string) $model->getId(), $nodesArray)) {
            $nodesArray[(string) $model->getId()] = $model->toArray();
        }
    }
}
