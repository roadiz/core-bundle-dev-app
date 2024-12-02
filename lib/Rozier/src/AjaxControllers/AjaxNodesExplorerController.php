<?php

declare(strict_types=1);

namespace Themes\Rozier\AjaxControllers;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\EntityApi\NodeTypeApi;
use RZ\Roadiz\CoreBundle\Explorer\AbstractExplorerItem;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerItemFactoryInterface;
use RZ\Roadiz\CoreBundle\SearchEngine\ClientRegistry;
use RZ\Roadiz\CoreBundle\SearchEngine\NodeSourceSearchHandlerInterface;
use RZ\Roadiz\CoreBundle\SearchEngine\SolrSearchResultItem;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Serializer\SerializerInterface;

final class AjaxNodesExplorerController extends AbstractAjaxController
{
    public function __construct(
        private readonly ExplorerItemFactoryInterface $explorerItemFactory,
        private readonly ClientRegistry $clientRegistry,
        private readonly NodeSourceSearchHandlerInterface $nodeSourceSearchHandler,
        private readonly NodeTypeApi $nodeTypeApi,
        SerializerInterface $serializer,
    ) {
        parent::__construct($serializer);
    }

    protected function getItemPerPage(): int
    {
        return 30;
    }

    protected function isSearchEngineAvailable(Request $request): bool
    {
        return '' !== $request->get('search') && null !== $this->clientRegistry->getClient();
    }

    /**
     * @return Response JSON response
     */
    public function indexAction(Request $request): Response
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
            'status' => ['<=', Node::ARCHIVED],
        ];

        if ($request->query->has('tagId') && $request->get('tagId') > 0) {
            $tag = $this->em()
                ->find(
                    Tag::class,
                    $request->get('tagId')
                );

            $arrayFilter['tags'] = [$tag];
        }

        if ($request->query->has('nodeTypes') && count($request->get('nodeTypes')) > 0) {
            $nodeTypeNames = array_map('trim', $request->get('nodeTypes'));

            $nodeTypes = $this->nodeTypeApi->getBy([
                'name' => $nodeTypeNames,
            ]);

            if (null !== $nodeTypes && count($nodeTypes) > 0) {
                $arrayFilter['nodeType'] = $nodeTypes;
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
     *
     * @throws NotSupported
     */
    public function listAction(Request $request): JsonResponse
    {
        // Only requires Search permission for nodes
        $this->denyAccessUnlessGranted(NodeVoter::SEARCH);

        if (!$request->query->has('ids')) {
            throw new InvalidParameterException('Ids should be provided within an array');
        }

        $cleanNodeIds = array_filter($request->query->filter('ids', [], \FILTER_DEFAULT, [
            'flags' => \FILTER_FORCE_ARRAY,
        ]));
        $nodesArray = [];

        if (count($cleanNodeIds)) {
            /** @var EntityManager $em */
            $em = $this->em();
            $nodes = $em->getRepository(Node::class)
                ->setDisplayingNotPublishedNodes(true)
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
     * @param iterable<Node|NodesSources|SolrSearchResultItem> $nodes
     *
     * @return array<AbstractExplorerItem>
     */
    private function normalizeNodes(iterable $nodes): array
    {
        $nodesArray = [];

        foreach ($nodes as $node) {
            if ($node instanceof SolrSearchResultItem) {
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
        if (!key_exists($model->getId(), $nodesArray)) {
            $nodesArray[$model->getId()] = $model->toArray();
        }
    }
}
