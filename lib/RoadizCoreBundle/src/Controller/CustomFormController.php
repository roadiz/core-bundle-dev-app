<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Controller;

use ApiPlatform\Metadata\Exception\OperationNotFoundException;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\FilesystemException;
use Limenius\Liform\LiformInterface;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\CustomForm\CustomFormHelperFactory;
use RZ\Roadiz\CoreBundle\CustomForm\Message\CustomFormAnswerNotifyMessage;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Exception\EntityAlreadyExistsException;
use RZ\Roadiz\CoreBundle\Form\Error\FormErrorSerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CustomFormController extends AbstractController
{
    public function __construct(
        private readonly Settings $settingsBag,
        private readonly LoggerInterface $logger,
        private readonly TranslatorInterface $translator,
        private readonly CustomFormHelperFactory $customFormHelperFactory,
        private readonly LiformInterface $liform,
        private readonly SerializerInterface $serializer,
        private readonly FormErrorSerializerInterface $formErrorSerializer,
        private readonly ManagerRegistry $registry,
        private readonly RateLimiterFactoryInterface $customFormLimiter,
        private readonly MessageBusInterface $messageBus,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        private readonly bool $useConstraintViolationList,
        private readonly string $customFormPostOperationName,
    ) {
    }

    /**
     * @phpstan-assert CustomForm $customForm
     * @phpstan-assert true $customForm->isFormStillOpen()
     */
    private function validateCustomForm(?CustomForm $customForm): void
    {
        if (null === $customForm) {
            throw new NotFoundHttpException('Custom form not found');
        }
        if (!$customForm->isFormStillOpen()) {
            throw new NotFoundHttpException('Custom form is closed');
        }
    }

    public function definitionAction(Request $request, int $id): JsonResponse
    {
        /** @var CustomForm|null $customForm */
        $customForm = $this->registry->getRepository(CustomForm::class)->find($id);
        $this->validateCustomForm($customForm);

        $helper = $this->customFormHelperFactory->createHelper($customForm);
        $schema = json_encode($this->liform->transform($helper->getForm($request, false, false)));

        return new JsonResponse(
            $schema,
            Response::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @throws \Exception|FilesystemException
     */
    public function postAction(Request $request, int $id): Response
    {
        // create a limiter based on a unique identifier of the client
        $limiter = $this->customFormLimiter->create($request->getClientIp());
        // only claims 1 token if it's free at this moment
        $limit = $limiter->consume();
        $headers = [
            'X-RateLimit-Remaining' => $limit->getRemainingTokens(),
            'X-RateLimit-Retry-After' => $limit->getRetryAfter()->getTimestamp(),
            'X-RateLimit-Limit' => $limit->getLimit(),
        ];
        if (false === $limit->isAccepted()) {
            throw new TooManyRequestsHttpException($limit->getRetryAfter()->getTimestamp());
        }

        /** @var CustomForm|null $customForm */
        $customForm = $this->registry->getRepository(CustomForm::class)->find($id);
        $this->validateCustomForm($customForm);

        $mixed = $this->prepareAndHandleCustomFormAssignation(
            $request,
            $customForm,
            new JsonResponse(null, Response::HTTP_ACCEPTED, $headers),
            prefix: false
        );

        if ($mixed instanceof Response) {
            $mixed->prepare($request);

            return $mixed;
        }

        if (
            !is_array($mixed)
            || !$mixed['formObject'] instanceof FormInterface
        ) {
            throw new \RuntimeException('Form handling did not return a valid array with a FormInterface instance.');
        }

        if (!$mixed['formObject']->isSubmitted()) {
            $mixed['formObject']->addError(new FormError('Form has not been submitted'));
        }

        if ($this->useConstraintViolationList) {
            try {
                $this->resourceMetadataCollectionFactory->create(
                    CustomForm::class
                )->getOperation($this->customFormPostOperationName);
                $request->attributes->set('_api_operation_name', $this->customFormPostOperationName);
                $request->attributes->set('_api_resource_class', CustomForm::class);
                throw new ValidationException($this->formErrorSerializer->getErrorsAsConstraintViolationList($mixed['formObject']));
            } catch (OperationNotFoundException) {
                // Do not use 422 response if api_contact_form_post operation does not exist
                $this->logger->warning(sprintf('Operation "%s" not found, falling back to legacy errors-as-array response.', $this->customFormPostOperationName));
            }
        }

        /*
         * Legacy form-error array response.
         */
        $errorPayload = [
            'status' => Response::HTTP_BAD_REQUEST,
            'errorsPerForm' => $this->formErrorSerializer->getErrorsAsArray($mixed['formObject']),
        ];

        return new JsonResponse(
            $this->serializer->serialize($errorPayload, 'json'),
            Response::HTTP_BAD_REQUEST,
            $headers,
            true
        );
    }

    /**
     * @throws FilesystemException
     */
    public function addAction(Request $request, int $customFormId): Response
    {
        /** @var CustomForm|null $customForm */
        $customForm = $this->registry->getRepository(CustomForm::class)->find($customFormId);
        $this->validateCustomForm($customForm);

        $mixed = $this->prepareAndHandleCustomFormAssignation(
            $request,
            $customForm,
            new RedirectResponse(
                $this->generateUrl(
                    'customFormSentAction',
                    ['customFormId' => $customFormId]
                )
            )
        );

        if ($mixed instanceof Response) {
            return $mixed;
        } else {
            return $this->render('@RoadizCore/customForm/customForm.html.twig', $mixed);
        }
    }

    public function sentAction(Request $request, int $customFormId): Response
    {
        $assignation = [];
        /** @var CustomForm|null $customForm */
        $customForm = $this->registry->getRepository(CustomForm::class)->find($customFormId);
        $this->validateCustomForm($customForm);

        $assignation['customForm'] = $customForm;

        return $this->render('@RoadizCore/customForm/customFormSent.html.twig', $assignation);
    }

    /**
     * Prepare and handle a CustomForm Form then send a confirmation email.
     *
     * * This method will return an assignation **array** if form is not validated.
     *     * customForm
     *     * fields
     *     * form
     * * If form is validated, **RedirectResponse** will be returned.
     *
     * @return array|Response
     *
     * @throws FilesystemException
     */
    private function prepareAndHandleCustomFormAssignation(
        Request $request,
        CustomForm $customFormsEntity,
        Response $response,
        bool $prefix = true,
    ) {
        $assignation = [
            'customForm' => $customFormsEntity,
            'fields' => $customFormsEntity->getFields(),
            'head' => [
                'siteTitle' => $this->settingsBag->get('site_name'),
            ],
        ];
        $helper = $this->customFormHelperFactory->createHelper($customFormsEntity);
        $form = $helper->getForm(
            $request,
            prefix: $prefix
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /*
                 * Parse form data and create answer.
                 */
                $answer = $helper->parseAnswerFormData($form, null, $request->getClientIp() ?? '');
                $answerId = $answer->getId();
                if (!is_int($answerId)) {
                    throw new \RuntimeException('Answer ID is null');
                }

                $this->messageBus->dispatch(new CustomFormAnswerNotifyMessage(
                    $answerId,
                    $this->translator->trans(
                        'new.answer.form.%site%',
                        ['%site%' => $customFormsEntity->getDisplayName()],
                        locale: $request->getLocale(),
                    ),
                    $request->getLocale()
                ));

                $msg = $this->translator->trans(
                    'customForm.%name%.send',
                    ['%name%' => $customFormsEntity->getDisplayName()]
                );

                $this->logger->info($msg);

                return $response;
            } catch (EntityAlreadyExistsException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        $assignation['form'] = $form->createView();
        $assignation['formObject'] = $form;

        return $assignation;
    }
}
