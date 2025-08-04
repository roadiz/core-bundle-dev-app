<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\InvalidParameterException;

final class AjaxCustomFormsExplorerController extends AbstractAjaxExplorerController
{
    public function indexAction(Request $request): JsonResponse
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

        return $this->createSerializedResponse([
            'status' => 'confirm',
            'statusCode' => 200,
            'customForms' => $customFormsArray,
            'customFormsCount' => count($customForms),
            'filters' => $listManager->getAssignation(),
        ]);
    }

    /**
     * Get a CustomForm list from an array of id.
     */
    public function listAction(Request $request): Response
    {
        if (!$request->query->has('ids')) {
            throw new InvalidParameterException('Ids should be provided within an array');
        }

        $this->denyAccessUnlessGranted('ROLE_ACCESS_CUSTOMFORMS');

        $cleanCustomFormsIds = array_filter($request->query->filter('ids', [], \FILTER_DEFAULT, [
            'flags' => \FILTER_FORCE_ARRAY,
        ]));
        $customFormsArray = [];

        if (count($cleanCustomFormsIds)) {
            $customForms = $this->managerRegistry->getRepository(CustomForm::class)->findBy([
                'id' => $cleanCustomFormsIds,
            ]);
            // Sort array by ids given in request
            $customForms = $this->sortIsh($customForms, $cleanCustomFormsIds);
            $customFormsArray = $this->normalizeCustomForms($customForms);
        }

        return $this->createSerializedResponse([
            'status' => 'confirm',
            'statusCode' => 200,
            'forms' => $customFormsArray,
        ]);
    }

    /**
     * Normalize response CustomForm list result.
     *
     * @param iterable<CustomForm> $customForms
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
