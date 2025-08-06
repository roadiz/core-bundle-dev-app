<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use RZ\Roadiz\CoreBundle\Entity\CustomFormField;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AjaxCustomFormFieldsController extends AjaxAbstractFieldsController
{
    /**
     * Handle AJAX edition requests for CustomFormFields
     * such as coming from widgets.
     *
     * @return Response JSON response
     */
    public function editAction(Request $request, int $customFormFieldId): Response
    {
        $this->validateRequest($request);
        $this->denyAccessUnlessGranted('ROLE_ACCESS_CUSTOMFORMS_DELETE');

        $field = $this->findEntity((int) $customFormFieldId);

        if (null !== $field && null !== $response = $this->handleFieldActions($request, $field)) {
            return $response;
        }

        throw $this->createNotFoundException($this->translator->trans('field.%customFormFieldId%.not_exists', ['%customFormFieldId%' => $customFormFieldId]));
    }

    #[\Override]
    protected function getEntityClass(): string
    {
        return CustomFormField::class;
    }
}
