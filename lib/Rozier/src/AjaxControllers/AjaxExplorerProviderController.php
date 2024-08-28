<?php

declare(strict_types=1);

namespace Themes\Rozier\AjaxControllers;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerItemInterface;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class AjaxExplorerProviderController extends AbstractAjaxController
{
    public function __construct(private readonly ContainerInterface $psrContainer)
    {
    }

    /**
     * @param class-string<ExplorerProviderInterface> $providerClass
     * @return ExplorerProviderInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getProvider(string $providerClass): ExplorerProviderInterface
    {
        if ($this->psrContainer->has($providerClass)) {
            return $this->psrContainer->get($providerClass);
        }
        return new $providerClass();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
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

        return $this->getProvider($providerClass);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function listAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_BACKEND_USER');

        $provider = $this->getProviderFromRequest($request);
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
