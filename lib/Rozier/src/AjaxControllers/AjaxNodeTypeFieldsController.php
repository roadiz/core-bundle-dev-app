<?php

declare(strict_types=1);

namespace Themes\Rozier\AjaxControllers;

use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AjaxNodeTypeFieldsController extends AjaxAbstractFieldsController
{
    /**
     * Handle AJAX edition requests for NodeTypeFields
     * such as coming from widgets.
     *
     * @return Response JSON response
     */
    public function editAction(Request $request, int $nodeTypeFieldId): Response
    {
        $this->validateRequest($request);
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODEFIELDS_DELETE');

        $field = $this->findEntity($nodeTypeFieldId);

        if (null !== $response = $this->handleFieldActions($request, $field)) {
            return $response;
        }

        throw $this->createNotFoundException($this->getTranslator()->trans('field.%nodeTypeFieldId%.not_exists', ['%nodeTypeFieldId%' => $nodeTypeFieldId]));
    }

    protected function getEntityClass(): string
    {
        return NodeTypeField::class;
    }
}
