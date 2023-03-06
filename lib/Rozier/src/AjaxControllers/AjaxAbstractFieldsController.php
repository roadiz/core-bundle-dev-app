<?php

declare(strict_types=1);

namespace Themes\Rozier\AjaxControllers;

use RZ\Roadiz\Core\AbstractEntities\AbstractField;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package Themes\Rozier\AjaxControllers
 */
abstract class AjaxAbstractFieldsController extends AbstractAjaxController
{
    private HandlerFactoryInterface $handlerFactory;

    /**
     * @param HandlerFactoryInterface $handlerFactory
     */
    public function __construct(HandlerFactoryInterface $handlerFactory)
    {
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * Handle actions for any abstract fields.
     *
     * @param Request       $request
     * @param AbstractField|null $field
     *
     * @return null|Response
     */
    protected function handleFieldActions(Request $request, AbstractField $field = null)
    {
        /*
         * Validate
         */
        $this->validateRequest($request);

        if ($field !== null) {
            $responseArray = null;

            /*
             * Get the right update method against "_action" parameter
             */
            switch ($request->get('_action')) {
                case 'updatePosition':
                    $responseArray = $this->updatePosition($request->request->all(), $field);
                    break;
            }

            if ($responseArray === null) {
                $responseArray = [
                    'statusCode' => '200',
                    'status' => 'success',
                    'responseText' => $this->getTranslator()->trans('field.%name%.updated', [
                        '%name%' => $field->getName(),
                    ]),
                ];
            }

            return new JsonResponse(
                $responseArray,
                Response::HTTP_PARTIAL_CONTENT
            );
        }

        return null;
    }

    /**
     * @param array $parameters
     * @param AbstractField|null $field
     *
     * @return array
     */
    protected function updatePosition(array $parameters, AbstractField $field = null): array
    {
        /*
         * First, we set the new parent
         */
        if (!empty($parameters['newPosition']) && null !== $field) {
            $field->setPosition((float) $parameters['newPosition']);
            // Apply position update before cleaning
            $this->em()->flush();
            $handler = $this->handlerFactory->getHandler($field);
            $handler->cleanPositions();
            $this->em()->flush();
            return [
                'statusCode' => '200',
                'status' => 'success',
                'responseText' => $this->getTranslator()->trans('field.%name%.updated', [
                    '%name%' => $field->getName(),
                ]),
            ];
        }
        return [
            'statusCode' => '400',
            'status' => 'error',
            'responseText' => $this->getTranslator()->trans('field.%name%.updated', [
                '%name%' => $field->getName(),
            ]),
        ];
    }
}
