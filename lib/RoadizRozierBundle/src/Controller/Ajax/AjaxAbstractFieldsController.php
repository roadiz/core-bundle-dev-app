<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Ajax;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Entity\AbstractField;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @deprecated use specific Ajax controllers for each Field type instead
 */
abstract class AjaxAbstractFieldsController extends AbstractAjaxController
{
    public function __construct(
        protected readonly HandlerFactoryInterface $handlerFactory,
        ManagerRegistry $managerRegistry,
        SerializerInterface $serializer,
        TranslatorInterface $translator,
    ) {
        parent::__construct($managerRegistry, $serializer, $translator);
    }

    protected function findEntity(int|string $entityId): ?AbstractField
    {
        return $this->managerRegistry->getRepository($this->getEntityClass())->find((int) $entityId);
    }

    /**
     * Handle actions for any abstract fields.
     */
    protected function handleFieldActions(Request $request, ?AbstractField $field = null): ?Response
    {
        $this->validateRequest($request);

        if (null === $field) {
            return null;
        }

        /*
         * Get the right update method against "_action" parameter
         */
        if ('updatePosition' !== $request->get('_action')) {
            throw new BadRequestHttpException('Action does not exist');
        }

        $responseArray = $this->updatePosition($request->request->all(), $field);

        return new JsonResponse(
            $responseArray,
            Response::HTTP_PARTIAL_CONTENT
        );
    }

    protected function updatePosition(array $parameters, ?AbstractField $field = null): array
    {
        if (!empty($parameters['afterFieldId']) && is_numeric($parameters['afterFieldId'])) {
            $afterField = $this->findEntity((int) $parameters['afterFieldId']);
            if (null === $afterField) {
                throw new BadRequestHttpException('afterFieldId does not exist');
            }
            $field->setPosition($afterField->getPosition() + 0.5);
            // Apply position update before cleaning
            $this->managerRegistry->getManager()->flush();
            $handler = $this->handlerFactory->getHandler($field);
            $handler->cleanPositions();
            $this->managerRegistry->getManager()->flush();

            return [
                'statusCode' => '200',
                'status' => 'success',
                'responseText' => $this->translator->trans('field.%name%.updated', [
                    '%name%' => $field->getName(),
                ]),
            ];
        }
        if (!empty($parameters['beforeFieldId']) && is_numeric($parameters['beforeFieldId'])) {
            $beforeField = $this->findEntity((int) $parameters['beforeFieldId']);
            if (null === $beforeField) {
                throw new BadRequestHttpException('beforeFieldId does not exist');
            }
            $field->setPosition($beforeField->getPosition() - 0.5);
            // Apply position update before cleaning
            $this->managerRegistry->getManager()->flush();
            $handler = $this->handlerFactory->getHandler($field);
            $handler->cleanPositions();
            $this->managerRegistry->getManager()->flush();

            return [
                'statusCode' => '200',
                'status' => 'success',
                'responseText' => $this->translator->trans('field.%name%.updated', [
                    '%name%' => $field->getName(),
                ]),
            ];
        }

        throw new BadRequestHttpException('Cannot update position for Field. Missing parameters.');
    }

    /**
     * @return class-string<AbstractField>
     */
    abstract protected function getEntityClass(): string;
}
