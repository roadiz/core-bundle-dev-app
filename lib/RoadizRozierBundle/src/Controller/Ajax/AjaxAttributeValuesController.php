<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Repository\AttributeValueRepository;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use RZ\Roadiz\RozierBundle\Model\PositionDto;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AjaxAttributeValuesController extends AbstractAjaxController
{
    use UpdatePositionTrait;

    public function __construct(
        private readonly AttributeValueRepository $attributeValueRepository,
        ManagerRegistry $managerRegistry,
        SerializerInterface $serializer,
        TranslatorInterface $translator,
    ) {
        parent::__construct($managerRegistry, $serializer, $translator);
    }

    /**
     * Handle AJAX edition requests for NodeTypeFields
     * such as coming from widgets.
     *
     * @return Response JSON response
     */
    #[Route(
        path: '/rz-admin/ajax/attribute-values/edit/position',
        name: 'attributeValuePositionAjax',
        methods: ['POST'],
        format: 'json',
    )]
    public function editPositionAction(
        #[MapRequestPayload]
        PositionDto $positionDto,
    ): Response {
        $this->validateCsrfToken($positionDto->csrfToken);

        $attributeValue = $this->attributeValueRepository->find($positionDto->id);
        if (null === $attributeValue) {
            throw new NotFoundHttpException('AttributeValue does not exist');
        }

        $this->denyAccessUnlessGranted(NodeVoter::EDIT_ATTRIBUTE, $attributeValue->getAttributable());

        $this->updatePosition($positionDto, $attributeValue, $this->attributeValueRepository);

        $this->managerRegistry->getManager()->flush();

        return new JsonResponse(
            [
                'statusCode' => '200',
                'status' => 'success',
                'responseText' => $this->translator->trans(
                    'attribute_value_translation.%name%.updated_from_node.%nodeName%',
                    [
                        '%name%' => $attributeValue->getAttribute()->getLabelOrCode(),
                        '%nodeName%' => $attributeValue->getAttributable()->getNodeName(),
                    ]
                ),
            ],
            Response::HTTP_PARTIAL_CONTENT
        );
    }
}
