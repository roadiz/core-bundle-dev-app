<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use RZ\Roadiz\CoreBundle\Entity\AttributeValue;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class AjaxAttributeValuesController extends AbstractAjaxController
{
    protected static array $validMethods = [
        Request::METHOD_POST,
    ];

    /**
     * Handle AJAX edition requests for NodeTypeFields
     * such as coming from widgets.
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
        $attributeValue = $this->managerRegistry
            ->getRepository(AttributeValue::class)
            ->find($attributeValueId);

        if (null === $attributeValue) {
            throw $this->createNotFoundException($this->translator->trans('attribute_value.%attributeValueId%.not_exists', ['%attributeValueId%' => $attributeValueId]));
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

    protected function updatePosition(array $parameters, AttributeValue $attributeValue): array
    {
        $attributable = $attributeValue->getAttributable();
        $details = [
            '%name%' => $attributeValue->getAttribute()->getLabelOrCode(),
            '%nodeName%' => $attributable->getNodeName(),
        ];

        if (!empty($parameters['afterAttributeValueId']) && is_numeric($parameters['afterAttributeValueId'])) {
            /** @var AttributeValue|null $afterAttributeValue */
            $afterAttributeValue = $this->managerRegistry
                ->getRepository(AttributeValue::class)
                ->find((int) $parameters['afterAttributeValueId']);
            if (null === $afterAttributeValue) {
                throw new BadRequestHttpException('afterAttributeValueId does not exist');
            }
            $attributeValue->setPosition($afterAttributeValue->getPosition() + 0.5);
            $this->managerRegistry->getManager()->flush();

            return [
                'statusCode' => '200',
                'status' => 'success',
                'responseText' => $this->translator->trans(
                    'attribute_value_translation.%name%.updated_from_node.%nodeName%',
                    $details
                ),
            ];
        }
        if (!empty($parameters['beforeAttributeValueId']) && is_numeric($parameters['beforeAttributeValueId'])) {
            /** @var AttributeValue|null $beforeAttributeValue */
            $beforeAttributeValue = $this->managerRegistry
                ->getRepository(AttributeValue::class)
                ->find((int) $parameters['beforeAttributeValueId']);
            if (null === $beforeAttributeValue) {
                throw new BadRequestHttpException('beforeAttributeValueId does not exist');
            }
            $attributeValue->setPosition($beforeAttributeValue->getPosition() - 0.5);
            $this->managerRegistry->getManager()->flush();

            return [
                'statusCode' => '200',
                'status' => 'success',
                'responseText' => $this->translator->trans(
                    'attribute_value_translation.%name%.updated_from_node.%nodeName%',
                    $details
                ),
            ];
        }

        throw new BadRequestHttpException('Cannot update position for AttributeValue. Missing parameters.');
    }
}
