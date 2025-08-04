<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerItemInterface;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerProviderInterface;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerProviderLocator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/*
 * This class will be final in Roadiz v2.6+
 */
class AjaxExplorerProviderController extends AbstractAjaxController
{
    public function __construct(
        private readonly ExplorerProviderLocator $explorerProviderLocator,
        ManagerRegistry $managerRegistry,
        SerializerInterface $serializer,
        TranslatorInterface $translator,
    ) {
        parent::__construct($managerRegistry, $serializer, $translator);
    }

    protected function getProviderFromRequest(Request $request): ExplorerProviderInterface
    {
        /** @var class-string<ExplorerProviderInterface>|null $providerClass */
        $providerClass = $request->query->get('providerClass');

        if (!\is_string($providerClass)) {
            throw new InvalidParameterException('providerClass parameter is missing.');
        }
        if (!\class_exists($providerClass)) {
            throw new InvalidParameterException('providerClass is not a valid class.');
        }

        $reflection = new \ReflectionClass($providerClass);
        if (!$reflection->implementsInterface(ExplorerProviderInterface::class)) {
            throw new InvalidParameterException('providerClass is not a valid ExplorerProviderInterface class.');
        }

        return $this->explorerProviderLocator->getProvider($providerClass);
    }

    public function indexAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_BACKEND_USER');

        $provider = $this->getProviderFromRequest($request);
        $options = [
            'page' => $request->query->get('page') ?: 1,
            'itemPerPage' => $request->query->get('itemPerPage') ?: 30,
            'search' => $request->query->get('search') ?: null,
        ];
        if ($request->query->has('options')) {
            $options = array_merge(
                array_filter($request->query->filter('options', [], \FILTER_DEFAULT, [
                    'flags' => \FILTER_FORCE_ARRAY,
                ])),
                $options
            );
        }
        $entities = $provider->getItems($options);

        $entitiesArray = [];
        foreach ($entities as $entity) {
            if ($entity instanceof ExplorerItemInterface) {
                $entitiesArray[] = $entity->toArray();
            }
        }

        return $this->createSerializedResponse([
            'status' => 'confirm',
            'statusCode' => 200,
            'entities' => $entitiesArray,
            'filters' => $provider->getFilters($options),
        ]);
    }

    /**
     * Get a Node list from an array of id.
     */
    public function listAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_BACKEND_USER');

        $provider = $this->getProviderFromRequest($request);
        $entitiesArray = [];
        $cleanNodeIds = array_filter($request->query->filter('ids', [], \FILTER_DEFAULT, [
            'flags' => \FILTER_FORCE_ARRAY,
        ]));
        $cleanNodeIds = array_filter($cleanNodeIds, function ($value) {
            $nullValues = ['null', null, 0, '0', false, 'false'];

            return !in_array($value, $nullValues, true);
        });

        if (count($cleanNodeIds) > 0) {
            $entities = $provider->getItemsById($cleanNodeIds);

            foreach ($entities as $entity) {
                if ($entity instanceof ExplorerItemInterface) {
                    $entitiesArray[] = $entity->toArray();
                }
            }
        }

        return $this->createSerializedResponse([
            'status' => 'confirm',
            'statusCode' => 200,
            'items' => $entitiesArray,
        ]);
    }
}
