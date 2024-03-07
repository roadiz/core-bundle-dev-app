<?php

declare(strict_types=1);

namespace Themes\Rozier\AjaxControllers;

use Doctrine\ORM\EntityManager;
use RZ\Roadiz\Core\AbstractEntities\AbstractField;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Configuration\JoinNodeTypeFieldConfiguration;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Entity\Setting;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\Documents\MediaFinders\EmbedFinderFactory;
use RZ\Roadiz\Documents\Renderer\RendererInterface;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Yaml\Yaml;
use Themes\Rozier\Explorer\ConfigurableExplorerItem;
use Themes\Rozier\Explorer\FolderExplorerItem;
use Themes\Rozier\Explorer\SettingExplorerItem;
use Themes\Rozier\Explorer\UserExplorerItem;

class AjaxEntitiesExplorerController extends AbstractAjaxController
{
    public function __construct(
        private readonly RendererInterface $renderer,
        private readonly DocumentUrlGeneratorInterface $documentUrlGenerator,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly EmbedFinderFactory $embedFinderFactory
    ) {
    }

    /**
     * @param NodeTypeField $nodeTypeField
     * @return array
     */
    protected function getFieldConfiguration(NodeTypeField $nodeTypeField): array
    {
        if (
            $nodeTypeField->getType() !== AbstractField::MANY_TO_MANY_T &&
            $nodeTypeField->getType() !== AbstractField::MANY_TO_ONE_T
        ) {
            throw new InvalidParameterException('nodeTypeField is not a valid entity join.');
        }

        $configs = [
            Yaml::parse($nodeTypeField->getDefaultValues() ?? ''),
        ];
        $processor = new Processor();
        $joinConfig = new JoinNodeTypeFieldConfiguration();

        return $processor->processConfiguration($joinConfig, $configs);
    }

    public function indexAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_BACKEND_USER');

        if (!$request->query->has('nodeTypeFieldId')) {
            throw new InvalidParameterException('nodeTypeFieldId parameter is missing.');
        }

        /** @var NodeTypeField $nodeTypeField */
        $nodeTypeField = $this->em()->find(NodeTypeField::class, $request->query->get('nodeTypeFieldId'));
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

        $responseArray = [
            'status' => 'confirm',
            'statusCode' => 200,
            'entities' => $entitiesArray,
            'filters' => $listManager->getAssignation(),
        ];

        return new JsonResponse(
            $responseArray
        );
    }

    public function listAction(Request $request): JsonResponse
    {
        if (!$request->query->has('nodeTypeFieldId')) {
            throw new InvalidParameterException('nodeTypeFieldId parameter is missing.');
        }

        if (!$request->query->has('ids')) {
            throw new InvalidParameterException('Ids should be provided within an array');
        }

        $this->denyAccessUnlessGranted('ROLE_BACKEND_USER');

        /** @var EntityManager $em */
        $em = $this->em();

        /** @var NodeTypeField $nodeTypeField */
        $nodeTypeField = $this->em()->find(NodeTypeField::class, $request->query->get('nodeTypeFieldId'));
        $configuration = $this->getFieldConfiguration($nodeTypeField);
        /** @var class-string<PersistableInterface> $className */
        $className = $configuration['classname'];

        $cleanNodeIds = array_filter($request->query->filter('ids', [], \FILTER_DEFAULT, [
            'flags' => \FILTER_FORCE_ARRAY
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

        $responseArray = [
            'status' => 'confirm',
            'statusCode' => 200,
            'items' => $entitiesArray
        ];

        return new JsonResponse(
            $responseArray
        );
    }

    /**
     * Normalize response Node list result.
     *
     * @param iterable<PersistableInterface> $entities
     * @param array $configuration
     * @return array<array>
     */
    private function normalizeEntities(iterable $entities, array &$configuration): array
    {
        $entitiesArray = [];

        /** @var PersistableInterface $entity */
        foreach ($entities as $entity) {
            if ($entity instanceof Folder) {
                $explorerItem = new FolderExplorerItem($entity, $this->urlGenerator);
            } elseif ($entity instanceof Setting) {
                $explorerItem = new SettingExplorerItem($entity, $this->urlGenerator);
            } elseif ($entity instanceof User) {
                $explorerItem = new UserExplorerItem($entity, $this->urlGenerator);
            } else {
                $explorerItem = new ConfigurableExplorerItem(
                    $entity,
                    $configuration,
                    $this->renderer,
                    $this->documentUrlGenerator,
                    $this->urlGenerator,
                    $this->embedFinderFactory
                );
            }
            $entitiesArray[] = $explorerItem->toArray();
        }

        return $entitiesArray;
    }
}
