<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use RZ\Roadiz\CoreBundle\Entity\CustomFormField;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AjaxCustomFormFieldsController extends AjaxAbstractFieldsController
{
    /**
     * Handle AJAX edition requests for CustomFormFields
     * such as coming from widgets.
     *
     * @return Response JSON response
     */
    #[Route(
        path: '/rz-admin/ajax/custom-forms/fields/edit/{customFormFieldId}',
        name: 'customFormFieldAjaxEdit',
        requirements: ['customFormFieldId' => '\d+'],
        format: 'json'
    )]
    public function editAction(
        Request $request,
        #[MapEntity(
            expr: 'repository.find(customFormFieldId)',
            evictCache: true,
            message: 'field.%customFormFieldId%.not_exists'
        )]
        CustomFormField $field,
    ): Response {
        $this->validateRequest($request);
        $this->denyAccessUnlessGranted('ROLE_ACCESS_CUSTOMFORMS_DELETE');

        if (null !== $response = $this->handleFieldActions($request, $field)) {
            return $response;
        }

        throw $this->createNotFoundException($this->translator->trans('field.%customFormFieldId%.not_exists', ['%customFormFieldId%' => $field->getId()]));
    }

    #[\Override]
    protected function getEntityClass(): string
    {
        return CustomFormField::class;
    }
}
