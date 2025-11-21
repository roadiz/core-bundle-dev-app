<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Repository\CustomFormFieldRepository;
use RZ\Roadiz\RozierBundle\Model\PositionDto;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AjaxCustomFormFieldsController extends AbstractAjaxController
{
    use UpdatePositionTrait;

    public function __construct(
        private readonly CustomFormFieldRepository $customFormFieldRepository,
        private readonly HandlerFactoryInterface $handlerFactory,
        ManagerRegistry $managerRegistry,
        SerializerInterface $serializer,
        TranslatorInterface $translator,
    ) {
        parent::__construct($managerRegistry, $serializer, $translator);
    }

    /**
     * Handle AJAX edition requests for CustomFormFields
     * such as coming from widgets.
     *
     * @return Response JSON response
     */
    #[Route(
        path: '/rz-admin/ajax/custom-forms/fields/edit/position',
        name: 'customFormFieldPositionAjax',
        methods: ['POST'],
        format: 'json'
    )]
    public function editAction(
        #[MapRequestPayload]
        PositionDto $positionDto,
    ): Response {
        $this->validateCsrfToken($positionDto->csrfToken);
        $this->denyAccessUnlessGranted('ROLE_ACCESS_CUSTOMFORMS_DELETE');

        $field = $this->customFormFieldRepository->find($positionDto->id);
        if (null === $field) {
            throw $this->createNotFoundException();
        }

        $this->updatePosition($positionDto, $field, $this->customFormFieldRepository);

        $this->managerRegistry->getManager()->flush();
        $handler = $this->handlerFactory->getHandler($field);
        $handler->cleanPositions();
        $this->managerRegistry->getManager()->flush();

        return new JsonResponse(
            [
                'statusCode' => '200',
                'status' => 'success',
                'responseText' => $this->translator->trans('field.%name%.updated', [
                    '%name%' => $field->getName(),
                ]),
            ],
            Response::HTTP_PARTIAL_CONTENT
        );
    }
}
