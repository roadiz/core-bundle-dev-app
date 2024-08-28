<?php

declare(strict_types=1);

namespace Themes\Rozier\AjaxControllers;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerItemFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\InvalidParameterException;

final class AjaxCustomFormsExplorerController extends AbstractAjaxController
{
    public function __construct(
        private readonly ExplorerItemFactoryInterface $explorerItemFactory,
    ) {
    }

    /**
     * @param Request $request
     *
     * @return Response JSON response
     */
    public function indexAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_CUSTOMFORMS');

        $arrayFilter = [];
        /*
         * Manage get request to filter list
         */
        $listManager = $this->createEntityListManager(
            CustomForm::class,
            $arrayFilter,
            ['createdAt' => 'DESC']
        );
        $listManager->setDisplayingNotPublishedNodes(true);
        $listManager->setItemPerPage(40);
        $listManager->handle();

        $customForms = $listManager->getEntities();

        $customFormsArray = $this->normalizeCustomForms($customForms);

        $responseArray = [
            'status' => 'confirm',
            'statusCode' => 200,
            'customForms' => $customFormsArray,
            'customFormsCount' => count($customForms),
            'filters' => $listManager->getAssignation(),
        ];

        return new JsonResponse(
            $responseArray
        );
    }

    /**
     * Get a CustomForm list from an array of id.
     *
     * @param Request $request
     * @return Response
     * @throws NotSupported
     */
    public function listAction(Request $request): Response
    {
        if (!$request->query->has('ids')) {
            throw new InvalidParameterException('Ids should be provided within an array');
        }

        $this->denyAccessUnlessGranted('ROLE_ACCESS_CUSTOMFORMS');

        $cleanCustomFormsIds = array_filter($request->query->filter('ids', [], \FILTER_DEFAULT, [
            'flags' => \FILTER_FORCE_ARRAY
        ]));
        $customFormsArray = [];

        if (count($cleanCustomFormsIds)) {
            /** @var EntityManager $em */
            $em = $this->em();
            $customForms = $em->getRepository(CustomForm::class)->findBy([
                'id' => $cleanCustomFormsIds,
            ]);
            // Sort array by ids given in request
            $customForms = $this->sortIsh($customForms, $cleanCustomFormsIds);
            $customFormsArray = $this->normalizeCustomForms($customForms);
        }

        $responseArray = [
            'status' => 'confirm',
            'statusCode' => 200,
            'forms' => $customFormsArray
        ];

        return new JsonResponse(
            $responseArray
        );
    }

    /**
     * Normalize response CustomForm list result.
     *
     * @param iterable<CustomForm> $customForms
     * @return array
     */
    private function normalizeCustomForms(iterable $customForms): array
    {
        $customFormsArray = [];

        foreach ($customForms as $customForm) {
            $customFormModel = $this->explorerItemFactory->createForEntity($customForm);
            $customFormsArray[] = $customFormModel->toArray();
        }

        return $customFormsArray;
    }
}
