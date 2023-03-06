<?php

declare(strict_types=1);

namespace Themes\Rozier\AjaxControllers;

use Psr\Container\ContainerInterface;
use RZ\Roadiz\CoreBundle\Explorer\AbstractExplorerProvider;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerItemInterface;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\InvalidParameterException;

/**
 * @package Themes\Rozier\AjaxControllers
 */
class AjaxExplorerProviderController extends AbstractAjaxController
{
    private ContainerInterface $psrContainer;

    public function __construct(ContainerInterface $psrContainer)
    {
        $this->psrContainer = $psrContainer;
    }

    /**
     * @param class-string $providerClass
     * @return ExplorerProviderInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function getProvider(string $providerClass): ExplorerProviderInterface
    {
        if ($this->psrContainer->has($providerClass)) {
            return $this->psrContainer->get($providerClass);
        }
        return new $providerClass();
    }
    /**
     * @param Request $request
     * @return Response JSON response
     */
    public function indexAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_BACKEND_USER');

        if (!$request->query->has('providerClass')) {
            throw new InvalidParameterException('providerClass parameter is missing.');
        }

        $providerClass = $request->query->get('providerClass');
        if (!class_exists($providerClass)) {
            throw new InvalidParameterException('providerClass is not a valid class.');
        }

        $provider = $this->getProvider($providerClass);
        if ($provider instanceof AbstractExplorerProvider) {
            $provider->setContainer($this->psrContainer);
        }
        $options = [
            'page' => $request->query->get('page') ?: 1,
            'itemPerPage' => $request->query->get('itemPerPage') ?: 30,
            'search' => $request->query->get('search') ?: null,
        ];
        if ($request->query->has('options')) {
            $options = array_merge(
                array_filter($request->query->filter('options', [], \FILTER_DEFAULT, [
                    'flags' => \FILTER_FORCE_ARRAY
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

        $responseArray = [
            'status' => 'confirm',
            'statusCode' => 200,
            'entities' => $entitiesArray,
            'filters' => $provider->getFilters($options),
        ];

        return new JsonResponse(
            $responseArray,
            Response::HTTP_PARTIAL_CONTENT
        );
    }

    /**
     * Get a Node list from an array of id.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        if (!$request->query->has('providerClass')) {
            throw new InvalidParameterException('providerClass parameter is missing.');
        }

        $providerClass = $request->query->get('providerClass');
        if (!class_exists($providerClass)) {
            throw new InvalidParameterException('providerClass is not a valid class.');
        }

        if (!$request->query->has('ids')) {
            throw new InvalidParameterException('Ids should be provided within an array');
        }

        $this->denyAccessUnlessGranted('ROLE_BACKEND_USER');

        $provider = $this->getProvider($providerClass);
        if ($provider instanceof AbstractExplorerProvider) {
            $provider->setContainer($this->psrContainer);
        }
        $entitiesArray = [];
        $cleanNodeIds = array_filter($request->query->filter('ids', [], \FILTER_DEFAULT, [
            'flags' => \FILTER_FORCE_ARRAY
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

        $responseArray = [
            'status' => 'confirm',
            'statusCode' => 200,
            'items' => $entitiesArray
        ];

        return new JsonResponse(
            $responseArray
        );
    }
}
