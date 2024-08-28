<?php

declare(strict_types=1);

namespace Themes\Rozier\AjaxControllers;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use JMS\Serializer\SerializerInterface;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerItemFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\InvalidParameterException;

final class AjaxNodeTypesController extends AbstractAjaxController
{
    public function __construct(
        private readonly ExplorerItemFactoryInterface $explorerItemFactory,
        SerializerInterface $serializer
    ) {
        parent::__construct($serializer);
    }

    /**
     * @param Request $request
     *
     * @return Response JSON response
     */
    public function indexAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODES');
        $arrayFilter = [];

        /*
         * Manage get request to filter list
         */
        $listManager = $this->createEntityListManager(
            NodeType::class,
            $arrayFilter
        );
        $listManager->setDisplayingNotPublishedNodes(true);
        $listManager->setItemPerPage(30);
        $listManager->handle();

        $nodeTypes = $listManager->getEntities();
        $documentsArray = $this->normalizeNodeType($nodeTypes);

        return $this->createSerializedResponse([
            'status' => 'confirm',
            'statusCode' => 200,
            'nodeTypes' => $documentsArray,
            'nodeTypesCount' => count($nodeTypes),
            'filters' => $listManager->getAssignation()
        ]);
    }

    /**
     * Get a NodeType list from an array of id.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws NotSupported
     */
    public function listAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODES');

        if (!$request->query->has('names')) {
            throw new InvalidParameterException('Names array should be provided within an array');
        }

        $cleanNodeTypesName = array_filter($request->query->filter('names', [], \FILTER_DEFAULT, [
            'flags' => \FILTER_FORCE_ARRAY
        ]));
        $nodesArray = [];

        if (count($cleanNodeTypesName)) {
            /** @var EntityManager $em */
            $em = $this->em();
            $nodeTypes = $em->getRepository(NodeType::class)->findBy([
                'name' => $cleanNodeTypesName
            ]);

            // Sort array by ids given in request
            $nodesArray = $this->normalizeNodeType($nodeTypes);
        }

        return $this->createSerializedResponse([
            'status' => 'confirm',
            'statusCode' => 200,
            'items' => $nodesArray
        ]);
    }

    /**
     * Normalize response NodeType list result.
     *
     * @param iterable<NodeType> $nodeTypes
     * @return array
     */
    private function normalizeNodeType(iterable $nodeTypes): array
    {
        $nodeTypesArray = [];

        foreach ($nodeTypes as $nodeType) {
            $nodeModel = $this->explorerItemFactory->createForEntity($nodeType);
            $nodeTypesArray[] = $nodeModel->toArray();
        }

        return $nodeTypesArray;
    }
}
