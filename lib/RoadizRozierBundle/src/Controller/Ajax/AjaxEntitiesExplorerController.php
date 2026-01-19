<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Configuration\JoinNodeTypeFieldConfiguration;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Enum\FieldType;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerItemFactoryInterface;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerFactoryInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AjaxEntitiesExplorerController extends AbstractAjaxExplorerController
{
    public function __construct(
        private readonly NodeTypes $nodeTypesBag,
        ExplorerItemFactoryInterface $explorerItemFactory,
        EventDispatcherInterface $eventDispatcher,
        EntityListManagerFactoryInterface $entityListManagerFactory,
        SerializerInterface $serializer,
        ManagerRegistry $managerRegistry,
        TranslatorInterface $translator,
    ) {
        parent::__construct($explorerItemFactory, $eventDispatcher, $entityListManagerFactory, $managerRegistry, $serializer, $translator);
    }

    protected function getFieldConfiguration(NodeTypeField $nodeTypeField): array
    {
        if (
            FieldType::MANY_TO_MANY_T !== $nodeTypeField->getType()
            && FieldType::MANY_TO_ONE_T !== $nodeTypeField->getType()
        ) {
            throw new BadRequestHttpException('nodeTypeField is not a valid entity join.');
        }

        $configs = [
            Yaml::parse($nodeTypeField->getDefaultValues() ?? ''),
        ];
        $processor = new Processor();
        $joinConfig = new JoinNodeTypeFieldConfiguration();

        return $processor->processConfiguration($joinConfig, $configs);
    }

    #[Route(
        path: '/rz-admin/ajax/entities/explore',
        name: 'entitiesAjaxExplorerPage',
        methods: ['GET'],
        format: 'json'
    )]
    public function indexAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_BACKEND_USER');

        if (!$request->query->has('nodeTypeFieldName')) {
            throw new BadRequestHttpException('nodeTypeFieldId parameter is missing.');
        }

        if (!$request->query->has('nodeTypeName')) {
            throw new BadRequestHttpException('nodeTypeName parameter is missing.');
        }

        $nodeTypeName = $request->query->get('nodeTypeName');
        if (!is_string($nodeTypeName)) {
            throw new \RuntimeException('nodeTypeName should be a string');
        }

        $nodeTypeFieldName = $request->query->get('nodeTypeFieldName');
        if (!is_string($nodeTypeFieldName)) {
            throw new \RuntimeException('nodeTypeFieldName should be a string');
        }

        $nodeTypeField = $this->nodeTypesBag->get($nodeTypeName)?->getFieldByName($nodeTypeFieldName);

        if (null === $nodeTypeField) {
            throw new BadRequestHttpException('nodeTypeField does not exist.');
        }

        $configuration = $this->getFieldConfiguration($nodeTypeField);
        /** @var class-string<PersistableInterface> $className */
        $className = $configuration['classname'];

        $orderBy = [];
        foreach ($configuration['orderBy'] as $order) {
            $orderBy[$order['field']] = $order['direction'];
        }

        $criteria = [];
        foreach ($configuration['where'] as $where) {
            $criteria[$where['field']] = $where['value'];
        }

        /*
         * Manage get request to filter list
         */
        $listManager = $this->createEntityListManager(
            $className,
            $criteria,
            $orderBy
        );
        $listManager->setDisplayingNotPublishedNodes(true);
        $listManager->setItemPerPage(30);
        $listManager->handle();
        $entities = $listManager->getEntities();

        $entitiesArray = $this->normalizeEntities($entities, $configuration);

        return $this->createSerializedResponse([
            'status' => 'confirm',
            'statusCode' => 200,
            'entities' => $entitiesArray,
            'filters' => $listManager->getAssignation(),
        ]);
    }

    #[Route(
        path: '/rz-admin/ajax/entities/list',
        name: 'entitiesAjaxByArray',
        methods: ['GET'],
        format: 'json'
    )]
    public function listAction(Request $request): JsonResponse
    {
        if (!$request->query->has('nodeTypeFieldName')) {
            throw new BadRequestHttpException('nodeTypeFieldName parameter is missing.');
        }

        if (!$request->query->has('ids')) {
            throw new BadRequestHttpException('Ids should be provided within an array');
        }

        $this->denyAccessUnlessGranted('ROLE_BACKEND_USER');

        $em = $this->managerRegistry->getManager();

        if (!$request->query->has('nodeTypeName')) {
            throw new BadRequestHttpException('nodeTypeName parameter is missing.');
        }

        $nodeTypeName = $request->query->get('nodeTypeName');
        if (!is_string($nodeTypeName)) {
            throw new \RuntimeException('nodeTypeName should be a string');
        }

        $nodeTypeFieldName = $request->query->get('nodeTypeFieldName');
        if (!is_string($nodeTypeFieldName)) {
            throw new \RuntimeException('nodeTypeFieldName should be a string');
        }

        $nodeTypeField = $this->nodeTypesBag->get($nodeTypeName)?->getFieldByName($nodeTypeFieldName);

        if (null === $nodeTypeField) {
            throw new BadRequestHttpException('nodeTypeField does not exist.');
        }

        $configuration = $this->getFieldConfiguration($nodeTypeField);
        /** @var class-string<PersistableInterface> $className */
        $className = $configuration['classname'];

        $cleanNodeIds = array_filter($request->query->filter('ids', [], \FILTER_DEFAULT, [
            'flags' => \FILTER_FORCE_ARRAY,
        ]));
        $entitiesArray = [];

        if (count($cleanNodeIds)) {
            $entities = $em->getRepository($className)->findBy([
                'id' => $cleanNodeIds,
            ]);

            // Sort array by ids given in request
            $entities = $this->sortIsh($entities, $cleanNodeIds);
            $entitiesArray = $this->normalizeEntities($entities, $configuration);
        }

        return $this->createSerializedResponse([
            'status' => 'confirm',
            'statusCode' => 200,
            'items' => $entitiesArray,
        ]);
    }

    /**
     * Normalize response Node list result.
     *
     * @param iterable<PersistableInterface> $entities
     *
     * @return array<array>
     */
    private function normalizeEntities(iterable $entities, array $configuration): array
    {
        $entitiesArray = [];

        foreach ($entities as $entity) {
            $explorerItem = $this->explorerItemFactory->createForEntity(
                $entity,
                $configuration
            );
            $entitiesArray[] = $explorerItem->toArray();
        }

        return $entitiesArray;
    }
}
