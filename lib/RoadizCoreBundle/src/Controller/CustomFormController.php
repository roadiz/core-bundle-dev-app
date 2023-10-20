<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Exception;
use League\Flysystem\FilesystemException;
use Limenius\Liform\LiformInterface;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\CustomForm\CustomFormHelperFactory;
use RZ\Roadiz\CoreBundle\CustomForm\Message\CustomFormAnswerNotifyMessage;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Exception\EntityAlreadyExistsException;
use RZ\Roadiz\CoreBundle\Form\Error\FormErrorSerializerInterface;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CustomFormController extends AbstractController
{
    private Settings $settingsBag;
    private LoggerInterface $logger;
    private TranslatorInterface $translator;
    private CustomFormHelperFactory $customFormHelperFactory;
    private LiformInterface $liform;
    private SerializerInterface $serializer;
    private FormErrorSerializerInterface $formErrorSerializer;
    private ManagerRegistry $registry;
    private RateLimiterFactory $customFormLimiter;
    private PreviewResolverInterface $previewResolver;
    private MessageBusInterface $messageBus;

    public function __construct(
        Settings $settingsBag,
        LoggerInterface $logger,
        TranslatorInterface $translator,
        CustomFormHelperFactory $customFormHelperFactory,
        LiformInterface $liform,
        SerializerInterface $serializer,
        FormErrorSerializerInterface $formErrorSerializer,
        ManagerRegistry $registry,
        RateLimiterFactory $customFormLimiter,
        PreviewResolverInterface $previewResolver,
        MessageBusInterface $messageBus,
    ) {
        $this->settingsBag = $settingsBag;
        $this->logger = $logger;
        $this->translator = $translator;
        $this->customFormHelperFactory = $customFormHelperFactory;
        $this->liform = $liform;
        $this->serializer = $serializer;
        $this->formErrorSerializer = $formErrorSerializer;
        $this->registry = $registry;
        $this->customFormLimiter = $customFormLimiter;
        $this->previewResolver = $previewResolver;
        $this->messageBus = $messageBus;
    }

    protected function validateCustomForm(?CustomForm $customForm): void
    {
        if (null === $customForm) {
            throw new NotFoundHttpException('Custom form not found');
        }
        if (!$customForm->isFormStillOpen()) {
            throw new NotFoundHttpException('Custom form is closed');
        }
    }

    protected function getTranslationFromRequest(?Request $request): TranslationInterface
    {
        $locale = null;

        if (null !== $request) {
            $locale = $request->query->get('_locale');

            /*
             * If no _locale query param is defined check Accept-Language header
             */
            if (null === $locale) {
                $locale = $request->getPreferredLanguage($this->getTranslationRepository()->getAllLocales());
            }
        }
        /*
         * Then fallback to default CMS locale
         */
        if (null === $locale) {
            $translation = $this->getTranslationRepository()->findDefault();
        } elseif ($this->previewResolver->isPreview()) {
            $translation = $this->getTranslationRepository()
                ->findOneByLocaleOrOverrideLocale((string) $locale);
        } else {
            $translation = $this->getTranslationRepository()
                ->findOneAvailableByLocaleOrOverrideLocale((string) $locale);
        }
        if (null === $translation) {
            throw new NotFoundHttpException('No translation for locale ' . $locale);
        }
        return $translation;
    }

    /**
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function definitionAction(Request $request, int $id): JsonResponse
    {
        /** @var CustomForm|null $customForm */
        $customForm = $this->registry->getRepository(CustomForm::class)->find($id);
        $this->validateCustomForm($customForm);

        $helper = $this->customFormHelperFactory->createHelper($customForm);
        $translation = $this->getTranslationFromRequest($request);
        $request->setLocale($translation->getPreferredLocale());
        if ($this->translator instanceof LocaleAwareInterface) {
            $this->translator->setLocale($translation->getPreferredLocale());
        }
        $schema = json_encode($this->liform->transform($helper->getForm($request, false, false)));

        return new JsonResponse(
            $schema,
            Response::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @param Request $request
     * @param int $id
     * @return Response
     * @throws Exception|FilesystemException
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

        $translation = $this->getTranslationFromRequest($request);
        $request->setLocale($translation->getPreferredLocale());
        if ($this->translator instanceof LocaleAwareInterface) {
            $this->translator->setLocale($translation->getPreferredLocale());
        }

        $mixed = $this->prepareAndHandleCustomFormAssignation(
            $request,
            $customForm,
            new JsonResponse(null, Response::HTTP_ACCEPTED, $headers),
            false,
            null,
            false
        );

        if ($mixed instanceof Response) {
            $mixed->prepare($request);
            return $mixed;
        }

        if (is_array($mixed) && $mixed['formObject'] instanceof FormInterface) {
            if ($mixed['formObject']->isSubmitted()) {
                $errorPayload = [
                    'status' => Response::HTTP_BAD_REQUEST,
                    'errorsPerForm' => $this->formErrorSerializer->getErrorsAsArray($mixed['formObject'])
                ];
                return new JsonResponse(
                    $this->serializer->serialize($errorPayload, 'json'),
                    Response::HTTP_BAD_REQUEST,
                    $headers,
                    true
                );
            }
        }

        throw new BadRequestHttpException('Form has not been submitted');
    }

    /**
     * @param Request $request
     * @param int $customFormId
     * @return Response
     * @throws FilesystemException
     */
    public function addAction(Request $request, int $customFormId): Response
    {
        /** @var CustomForm $customForm */
        $customForm = $this->registry->getRepository(CustomForm::class)->find($customFormId);
        $this->validateCustomForm($customForm);

        $mixed = $this->prepareAndHandleCustomFormAssignation(
            $request,
            $customForm,
            new RedirectResponse(
                $this->generateUrl(
                    'customFormSentAction',
                    ["customFormId" => $customFormId]
                )
            )
        );

        if ($mixed instanceof Response) {
            $mixed->prepare($request);
            return $mixed->send();
        } else {
            return $this->render('@RoadizCore/customForm/customForm.html.twig', $mixed);
        }
    }

    /**
     * @param Request $request
     * @param int $customFormId
     * @return Response
     */
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
     * @param Request $request
     * @param CustomForm $customFormsEntity
     * @param Response $response
     * @param boolean $forceExpanded
     * @param string|null $emailSender
     * @param bool $prefix
     * @return array|Response
     * @throws FilesystemException
     */
    public function prepareAndHandleCustomFormAssignation(
        Request $request,
        CustomForm $customFormsEntity,
        Response $response,
        bool $forceExpanded = false,
        ?string $emailSender = null,
        bool $prefix = true
    ) {
        $assignation = [];
        $assignation['customForm'] = $customFormsEntity;
        $assignation['fields'] = $customFormsEntity->getFields();
        $helper = $this->customFormHelperFactory->createHelper($customFormsEntity);
        $form = $helper->getForm(
            $request,
            $forceExpanded,
            $prefix
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /*
                 * Parse form data and create answer.
                 */
                $answer = $helper->parseAnswerFormData($form, null, $request->getClientIp());

                $answerId = $answer->getId();
                if (!is_int($answerId)) {
                    throw new \RuntimeException('Answer ID is null');
                }

                if (null === $emailSender || false === filter_var($emailSender, FILTER_VALIDATE_EMAIL)) {
                    $emailSender = $this->settingsBag->get('email_sender');
                }

                $this->messageBus->dispatch(new CustomFormAnswerNotifyMessage(
                    $answerId,
                    $this->translator->trans(
                        'new.answer.form.%site%',
                        ['%site%' => $customFormsEntity->getDisplayName()]
                    ),
                    $emailSender,
                    $request->getLocale()
                ));

                $msg = $this->translator->trans(
                    'customForm.%name%.send',
                    ['%name%' => $customFormsEntity->getDisplayName()]
                );

                $session = $request->getSession();
                if ($session instanceof Session) {
                    $session->getFlashBag()->add('confirm', $msg);
                }
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

    protected function getTranslationRepository(): TranslationRepository
    {
        $repository = $this->registry->getRepository(TranslationInterface::class);
        if (!$repository instanceof TranslationRepository) {
            throw new \RuntimeException(
                'Translation repository must be instance of ' .
                TranslationRepository::class
            );
        }
        return $repository;
    }
}
