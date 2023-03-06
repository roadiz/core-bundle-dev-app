<?php

declare(strict_types=1);

namespace Themes\Rozier\AjaxControllers;

use Doctrine\ORM\EntityManager;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Themes\Rozier\Models\CustomFormModel;

/**
 * @package Themes\Rozier\AjaxControllers
 */
class AjaxCustomFormsExplorerController extends AbstractAjaxController
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Request $request
     *
     * @return Response JSON response
     */
    public function indexAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODES');

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
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        if (!$request->query->has('ids')) {
            throw new InvalidParameterException('Ids should be provided within an array');
        }

        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODES');

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
     * @param array<CustomForm>|\Traversable<CustomForm> $customForms
     * @return array
     */
    private function normalizeCustomForms($customForms)
    {
        $customFormsArray = [];

        /** @var CustomForm $customForm */
        foreach ($customForms as $customForm) {
            $customFormModel = new CustomFormModel($customForm, $this->urlGenerator, $this->getTranslator());
            $customFormsArray[] = $customFormModel->toArray();
        }

        return $customFormsArray;
    }
}
