<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Bag\DecoratedNodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerItemFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AjaxNodeTypesController extends AbstractAjaxController
{
    public function __construct(
        private readonly DecoratedNodeTypes $nodeTypesBag,
        private readonly ExplorerItemFactoryInterface $explorerItemFactory,
        ManagerRegistry $managerRegistry,
        SerializerInterface $serializer,
        TranslatorInterface $translator,
    ) {
        parent::__construct($managerRegistry, $serializer, $translator);
    }

    public function indexAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODES');
        $nodeTypes = $this->nodeTypesBag->all();
        $documentsArray = $this->normalizeNodeType($nodeTypes);

        return $this->createSerializedResponse([
            'status' => 'confirm',
            'statusCode' => 200,
            'nodeTypes' => $documentsArray,
            'nodeTypesCount' => count($nodeTypes),
            'filters' => [],
        ]);
    }

    /**
     * Get a NodeType list from an array of id.
     */
    public function listAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODES');

        if (!$request->query->has('names')) {
            throw new InvalidParameterException('Names array should be provided within an array');
        }

        $cleanNodeTypesName = array_filter($request->query->filter('names', [], \FILTER_DEFAULT, [
            'flags' => \FILTER_FORCE_ARRAY,
        ]));
        $nodesArray = [];

        if (count($cleanNodeTypesName)) {
            $nodeTypes = array_values(array_filter(array_map(fn (string $name) => $this->nodeTypesBag->get($name), $cleanNodeTypesName)));
            // Sort array by ids given in request
            $nodesArray = $this->normalizeNodeType($nodeTypes);
        }

        return $this->createSerializedResponse([
            'status' => 'confirm',
            'statusCode' => 200,
            'items' => $nodesArray,
        ]);
    }

    /**
     * Normalize response NodeType list result.
     *
     * @param iterable<NodeType> $nodeTypes
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
