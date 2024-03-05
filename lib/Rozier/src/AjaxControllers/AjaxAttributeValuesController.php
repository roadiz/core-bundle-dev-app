<?php

declare(strict_types=1);

namespace Themes\Rozier\AjaxControllers;

use RZ\Roadiz\CoreBundle\Entity\AttributeValue;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AjaxAttributeValuesController extends AbstractAjaxController
{
    protected static array $validMethods = [
        Request::METHOD_POST,
    ];

    /**
     * Handle AJAX edition requests for NodeTypeFields
     * such as coming from widgets.
     *
     * @param Request $request
     * @param int     $attributeValueId
     *
     * @return Response JSON response
     */
    public function editAction(Request $request, int $attributeValueId): Response
    {
        /*
         * Validate
         */
        $this->validateRequest($request, 'POST', false);

        /** @var AttributeValue|null $attributeValue */
        $attributeValue = $this->em()->find(AttributeValue::class, (int) $attributeValueId);

        if ($attributeValue === null) {
            throw $this->createNotFoundException($this->getTranslator()->trans(
                'attribute_value.%attributeValueId%.not_exists',
                [
                    '%attributeValueId%' => $attributeValueId
                ]
            ));
        }

        $this->denyAccessUnlessGranted(NodeVoter::EDIT_ATTRIBUTE, $attributeValue->getAttributable());

        $responseArray = [];
        /*
         * Get the right update method against "_action" parameter
         */
        switch ($request->get('_action')) {
            case 'updatePosition':
                $responseArray = $this->updatePosition($request->request->all(), $attributeValue);
                break;
        }

        return new JsonResponse(
            $responseArray,
            Response::HTTP_PARTIAL_CONTENT
        );
    }

    /**
     * @param array $parameters
     * @param AttributeValue $attributeValue
     *
     * @return array
     */
    protected function updatePosition(array $parameters, AttributeValue $attributeValue): array
    {
        $attributable = $attributeValue->getAttributable();
        $details = [
            '%name%' => $attributeValue->getAttribute()->getLabelOrCode(),
            '%nodeName%' => $attributable->getNodeName(),
        ];
        /*
         * First, we set the new parent
         */
        if (!empty($parameters['newPosition'])) {
            $attributeValue->setPosition((float) $parameters['newPosition']);
            // Apply position update before cleaning
            $this->em()->flush();
            return [
                'statusCode' => '200',
                'status' => 'success',
                'responseText' => $this->getTranslator()->trans(
                    'attribute_value_translation.%name%.updated_from_node.%nodeName%',
                    $details
                ),
            ];
        }

        return [
            'statusCode' => '400',
            'status' => 'error',
            'responseText' => $this->getTranslator()->trans(
                'attribute_value_translation.%name%.updated_from_node.%nodeName%',
                $details
            ),
        ];
    }
}
