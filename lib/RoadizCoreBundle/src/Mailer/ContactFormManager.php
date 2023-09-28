<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Mailer;

use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Exception\BadFormRequestException;
use RZ\Roadiz\CoreBundle\Form\Constraint\Recaptcha;
use RZ\Roadiz\CoreBundle\Form\Error\FormErrorSerializerInterface;
use RZ\Roadiz\CoreBundle\Form\HoneypotType;
use RZ\Roadiz\CoreBundle\Form\RecaptchaType;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class ContactFormManager extends EmailManager
{
    protected string $formName = 'contact_form';
    protected ?array $uploadedFiles = null;
    protected ?string $redirectUrl = null;
    protected ?FormBuilderInterface $formBuilder = null;
    protected ?FormInterface $form = null;
    protected array $options = [];
    protected string $method = Request::METHOD_POST;
    protected bool $emailStrictMode = false;
    protected bool $useRealResponseCode = false;
    protected array $allowedMimeTypes = [
        'application/pdf',
        'application/x-pdf',
        'image/jpeg',
        'image/png',
        'image/gif',
    ];
    protected int $maxFileSize = 5242880; // 5MB
    protected FormFactoryInterface $formFactory;
    protected FormErrorSerializerInterface $formErrorSerializer;
    protected ?string $recaptchaPrivateKey;
    protected ?string $recaptchaPublicKey;

    /*
     * DO NOT DIRECTLY USE THIS CONSTRUCTOR
     * USE 'contactFormManager' Factory Service
     */
    public function __construct(
        RequestStack $requestStack,
        FormFactoryInterface $formFactory,
        TranslatorInterface $translator,
        Environment $templating,
        MailerInterface $mailer,
        Settings $settingsBag,
        DocumentUrlGeneratorInterface $documentUrlGenerator,
        FormErrorSerializerInterface $formErrorSerializer,
        ?string $recaptchaPrivateKey,
        ?string $recaptchaPublicKey
    ) {
        parent::__construct($requestStack, $translator, $templating, $mailer, $settingsBag, $documentUrlGenerator);

        $this->formFactory = $formFactory;
        $this->formErrorSerializer = $formErrorSerializer;
        $this->options = [
            'attr' => [
                'id' => 'contactForm',
            ],
        ];

        $this->successMessage = 'form.successfully.sent';
        $this->failMessage = 'form.has.errors';
        $this->emailTemplate = '@RoadizCore/email/forms/contactForm.html.twig';
        $this->emailPlainTextTemplate = '@RoadizCore/email/forms/contactForm.txt.twig';

        $this->setSubject($this->translator->trans(
            'new.contact.form.%site%',
            ['%site%' => $this->settingsBag->get('site_name')]
        ));

        $this->setEmailTitle($this->translator->trans(
            'new.contact.form.%site%',
            ['%site%' => $this->settingsBag->get('site_name')]
        ));
        $this->recaptchaPrivateKey = $recaptchaPrivateKey;
        $this->recaptchaPublicKey = $recaptchaPublicKey;
    }

    /**
     * @return string
     */
    public function getFormName(): string
    {
        return $this->formName;
    }

    /**
     * Use this method BEFORE withDefaultFields()
     *
     * @param string $formName
     * @return ContactFormManager
     */
    public function setFormName(string $formName): ContactFormManager
    {
        $this->formName = $formName;
        return $this;
    }

    /**
     * Use this method BEFORE withDefaultFields()
     *
     * @return $this
     */
    public function disableCsrfProtection()
    {
        $this->options['csrf_protection'] = false;
        return $this;
    }

    /**
     * @return FormInterface
     */
    public function getForm(): FormInterface
    {
        return $this->form;
    }

    /**
     * Using the strict mode requires the "egulias/email-validator" library.
     *
     * Use this method BEFORE withDefaultFields()
     *
     * @param bool $emailStrictMode
     * @see https://symfony.com/doc/4.4/reference/constraints/Email.html#strict
     * @return $this
     */
    public function setEmailStrictMode(bool $emailStrictMode = true)
    {
        $this->emailStrictMode = $emailStrictMode;

        return $this;
    }
    /**
     * @return bool
     */
    public function isEmailStrictMode(): bool
    {
        return $this->emailStrictMode;
    }

    /**
     * Adds email, name and message fields with their constraints.
     *
     * @param bool $useHoneypot
     * @return ContactFormManager $this
     */
    public function withDefaultFields(bool $useHoneypot = true)
    {
        $this->getFormBuilder()->add('email', EmailType::class, [
            'label' => 'your.email',
            'constraints' => [
                new NotNull(),
                new NotBlank(),
                new Email([
                    'message' => 'email.not.valid',
                    'mode' => $this->isEmailStrictMode() ?
                        Email::VALIDATION_MODE_STRICT :
                        Email::VALIDATION_MODE_LOOSE
                ]),
            ],
        ])
            ->add('name', TextType::class, [
                'label' => 'your.name',
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'your.message',
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                ],
            ])
        ;

        if ($useHoneypot) {
            $this->withHoneypot();
        }

        return $this;
    }

    /**
     * Use this method AFTER withDefaultFields()
     *
     * @param string $honeypotName
     * @return $this
     */
    public function withHoneypot(string $honeypotName = 'eml')
    {
        $this->getFormBuilder()->add($honeypotName, HoneypotType::class);
        return $this;
    }

    /**
     * Use this method AFTER withDefaultFields()
     *
     * @param string $consentDescription
     * @return $this
     */
    public function withUserConsent(string $consentDescription = 'contact_form.user_consent')
    {
        $this->getFormBuilder()->add('consent', CheckboxType::class, [
            'label' => $consentDescription,
            'required' => true,
            'constraints' => [
                new NotBlank([
                    'message' => 'contact_form.must_consent_to_send',
                ]),
            ],
        ]);
        return $this;
    }

    /**
     * @return FormBuilderInterface
     */
    public function getFormBuilder(): FormBuilderInterface
    {
        if (null === $this->formBuilder) {
            $this->formBuilder = $this->formFactory
                ->createNamedBuilder($this->getFormName(), FormType::class, null, $this->options)
                ->setMethod($this->method);
        }
        return $this->formBuilder;
    }

    /**
     * Add a Google recaptcha to your contact form.
     *
     * Make sure you’ve added recaptcha form template and filled
     * recaptcha_public_key and recaptcha_private_key settings.
     *
     *   <script src='https://www.google.com/recaptcha/api.js'></script>
     *
     *   {% block recaptcha_widget -%}
     *       <div class="g-recaptcha" data-sitekey="{{ configs.publicKey }}"></div>
     *   {%- endblock recaptcha_widget %}
     *
     * If you are using API REST POST form, use 'g-recaptcha-response' name
     * to enable Validator to get challenge value.
     *
     * @return ContactFormManager
     */
    public function withGoogleRecaptcha(
        string $name = 'recaptcha',
        string $validatorFieldName = Recaptcha::FORM_NAME
    ) {
        if (
            !empty($this->recaptchaPublicKey) &&
            !empty($this->recaptchaPrivateKey)
        ) {
            $this->getFormBuilder()->add($name, RecaptchaType::class, [
                'label' => false,
                'configs' => [
                    'publicKey' => $this->recaptchaPublicKey,
                ],
                'constraints' => [
                    new Recaptcha([
                        'fieldName' => $validatorFieldName,
                        'privateKey' => $this->recaptchaPrivateKey,
                    ]),
                ],
            ]);
        }

        return $this;
    }

    /**
     * Handle custom form validation and send it as an email.
     *
     * @param callable|null $onValid
     * @return Response|null
     * @throws \Exception
     */
    public function handle(?callable $onValid = null): ?Response
    {
        $request = $this->requestStack->getMainRequest();
        if (null === $request) {
            throw new \RuntimeException('Main request is null');
        }
        $this->form = $this->getFormBuilder()->getForm();
        $this->form->handleRequest($request);
        $returnJson = $request->isXmlHttpRequest() ||
            $request->getRequestFormat() === 'json' ||
            (count($request->getAcceptableContentTypes()) === 1 && $request->getAcceptableContentTypes()[0] === 'application/json') ||
            ($request->attributes->has('_format') && $request->attributes->get('_format') === 'json');

        if ($this->form->isSubmitted()) {
            if ($this->form->isValid()) {
                try {
                    if (null !== $onValid) {
                        $onValid($this->form);
                    }

                    $this->handleFiles();
                    $this->handleFormData($this->form);
                    $this->send();
                    if ($returnJson) {
                        return new JsonResponse([], Response::HTTP_ACCEPTED);
                    } else {
                        if ($request->hasPreviousSession()) {
                            /** @var Session $session */
                            $session = $request->getSession();
                            $session->getFlashBag()
                                ->add('confirm', $this->translator->trans($this->successMessage));
                        }

                        $this->redirectUrl = $this->redirectUrl !== null ? $this->redirectUrl : $request->getUri();
                        return new RedirectResponse($this->redirectUrl);
                    }
                } catch (BadFormRequestException $e) {
                    if (null !== $e->getFieldErrored() && $this->form->has($e->getFieldErrored())) {
                        $this->form->get($e->getFieldErrored())->addError(new FormError($e->getMessage()));
                    } else {
                        $this->form->addError(new FormError($e->getMessage()));
                    }
                } catch (TransportExceptionInterface $exception) {
                    $this->form->addError(new FormError('Contact form could not be sent.'));
                }
            }
            if ($returnJson) {
                /*
                 * If form has errors during AJAX
                 * request we sent them.
                 */
                $errorPerForm = $this->formErrorSerializer->getErrorsAsArray($this->form);
                $responseArray = [
                    'status' => Response::HTTP_BAD_REQUEST,
                    'message' => $this->translator->trans($this->failMessage),
                    'errors' => (string) $this->form->getErrors(),
                    'errorsPerForm' => $errorPerForm,
                ];
                /*
                 * BC: Still return 200 if form is not valid for Ajax forms
                 */
                return new JsonResponse(
                    $responseArray,
                    $this->useRealResponseCode() ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK
                );
            }
        }
        return null;
    }

    protected function handleFiles(): void
    {
        $this->uploadedFiles = [];
        $request = $this->requestStack->getMainRequest();
        if (null === $request) {
            return;
        }
        /*
         * Files values
         */
        foreach ($request->files as $files) {
            /**
             * @var string $name
             * @var UploadedFile|array $uploadedFile
             */
            foreach ($files as $name => $uploadedFile) {
                if (null !== $uploadedFile) {
                    if (is_array($uploadedFile)) {
                        /**
                         * @var string $singleName
                         * @var UploadedFile|array $singleUploadedFile
                         */
                        foreach ($uploadedFile as $singleName => $singleUploadedFile) {
                            if (is_array($singleUploadedFile)) {
                                /**
                                 * @var string $singleName2
                                 * @var UploadedFile $singleUploadedFile2
                                 */
                                foreach ($singleUploadedFile as $singleName2 => $singleUploadedFile2) {
                                    $this->addUploadedFile($singleName2, $singleUploadedFile2);
                                }
                            } else {
                                $this->addUploadedFile($singleName, $singleUploadedFile);
                            }
                        }
                    } else {
                        $this->addUploadedFile($name, $uploadedFile);
                    }
                }
            }
        }
    }

    /**
     * @param string $name
     * @param UploadedFile $uploadedFile
     * @return $this
     * @throws BadFormRequestException
     */
    protected function addUploadedFile(string $name, UploadedFile $uploadedFile): ContactFormManager
    {
        if (
            !$uploadedFile->isValid() ||
            !in_array($uploadedFile->getMimeType(), $this->allowedMimeTypes) ||
            $uploadedFile->getSize() > $this->maxFileSize
        ) {
            throw new BadFormRequestException(
                $this->translator->trans('file.not.accepted'),
                Response::HTTP_FORBIDDEN,
                'danger',
                $name
            );
        } else {
            $this->uploadedFiles[$name] = $uploadedFile;
        }

        return $this;
    }

    /**
     * @param array $formData
     * @return string|null
     */
    protected function findEmailData(array $formData): ?string
    {
        foreach ($formData as $key => $value) {
            if (
                (new UnicodeString($key))->containsAny('email') &&
                is_string($value) &&
                filter_var($value, FILTER_VALIDATE_EMAIL)
            ) {
                return $value;
            } elseif (is_array($value) && null !== $email = $this->findEmailData($value)) {
                return $email;
            }
        }
        return null;
    }

    /**
     * @param FormInterface $form
     *
     * @throws \Exception
     */
    protected function handleFormData(FormInterface $form): void
    {
        $formData = $form->getData();
        $fields = $this->flattenFormData($form, []);

        /*
         * Sender email
         */
        $emailData = $this->findEmailData($formData);
        if (!empty($emailData)) {
            $this->setSender($emailData);
        }

        /**
         * @var string $key
         * @var UploadedFile $uploadedFile
         */
        foreach ($this->uploadedFiles as $key => $uploadedFile) {
            $fields[] = [
                'name' => strip_tags((string) $key),
                'value' => (strip_tags($uploadedFile->getClientOriginalName()) .
                    ' [' . $uploadedFile->guessExtension() . ']'),
            ];
        }
        /*
         * Date
         */
        $fields[] = [
            'name' => $this->translator->trans('date'),
            'value' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];
        /*
         * IP
         */
        $fields[] = [
            'name' => $this->translator->trans('ip.address'),
            'value' => $this->requestStack->getMainRequest()->getClientIp(),
        ];

        $this->assignation = [
            'mailContact' => $this->settingsBag->get('email_sender'),
            'emailType' => $this->getEmailType(),
            'title' => $this->getEmailTitle(),
            'email' => $this->getSender(),
            'fields' => $fields,
        ];
    }

    protected function isFieldPrivate(FormInterface $form): bool
    {
        $key = $form->getName();
        $privateFieldNames = [
            Recaptcha::FORM_NAME,
            'recaptcha'
        ];
        return
            is_string($key) &&
            (\mb_substr($key, 0, 1) === '_' || \in_array($key, $privateFieldNames))
        ;
    }

    /**
     * @param FormInterface $form
     * @param array $fields
     * @return array
     */
    protected function flattenFormData(FormInterface $form, array $fields): array
    {
        /** @var FormInterface $formItem */
        foreach ($form as $formItem) {
            $key = $formItem->getName();
            $value = $formItem->getData();
            $displayName = $formItem->getConfig()->getOption("label") ??
                (is_numeric($key) ? null : strip_tags(trim((string) $key)));

            if ($this->isFieldPrivate($formItem) || $value instanceof UploadedFile) {
                continue;
            } elseif ($formItem->count() > 0) {
                if (!empty($displayName)) {
                    $fields[] = [
                        'name' => $displayName,
                        'value' => null,
                    ];
                }
                $fields = $this->flattenFormData($formItem, $fields);
            } elseif (!empty($value)) {
                if ($value instanceof \DateTimeInterface) {
                    $displayValue = $value->format('Y-m-d H:i:s');
                } else {
                    $displayValue = strip_tags(trim((string) $value));
                }

                $fields[] = [
                    'name' => $displayName,
                    'value' => $displayValue,
                ];
            }
        }

        return $fields;
    }


    /**
     * Send contact form data by email.
     *
     * @return void
     * @throws \RuntimeException
     */
    public function send(): void
    {
        if (empty($this->assignation)) {
            throw new \RuntimeException("Can’t send a contact form without data.");
        }

        $this->message = $this->createMessage();

        /*
         * As this is a contact form
         * email receiver is website owner or custom.
         *
         * So you must return error email to receiver instead
         * of sender (who is your visitor).
         */
        $this->message->to(...$this->getReceiver());
        $this->message->returnPath($this->getReceiverEmail());

        /** @var UploadedFile $uploadedFile */
        foreach ($this->uploadedFiles as $uploadedFile) {
            $this->message->attachFromPath($uploadedFile->getRealPath(), $uploadedFile->getClientOriginalName());
        }

        // Send the message
        $this->mailer->send($this->message);
    }

    /**
     * @return null|array<Address>
     */
    public function getReceiver(): ?array
    {
        if (empty($this->settingsBag->get('email_sender'))) {
            throw new \InvalidArgumentException('Main "email_sender" is not configured for this website.');
        }
        $defaultReceivers = [new Address($this->settingsBag->get('email_sender'))];
        return parent::getReceiver() ?? $defaultReceivers;
    }

    /**
     * Gets the value of redirectUrl.
     *
     * @return string|null
     */
    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    /**
     * Sets the value of redirectUrl.
     *
     * @param string|null $redirectUrl Redirect url
     *
     * @return self
     */
    public function setRedirectUrl(?string $redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    /**
     * Gets the value of maxFileSize.
     *
     * @return int
     */
    public function getMaxFileSize()
    {
        return $this->maxFileSize;
    }

    /**
     * Sets the value of maxFileSize.
     *
     * @param int $maxFileSize the max file size
     *
     * @return self
     */
    public function setMaxFileSize($maxFileSize)
    {
        $this->maxFileSize = (int) $maxFileSize;

        return $this;
    }

    /**
     * Gets the value of allowedMimeTypes.
     *
     * @return array
     */
    public function getAllowedMimeTypes()
    {
        return $this->allowedMimeTypes;
    }

    /**
     * Sets the value of allowedMimeTypes.
     *
     * @param array $allowedMimeTypes the allowed mime types
     *
     * @return self
     */
    public function setAllowedMimeTypes(array $allowedMimeTypes)
    {
        $this->allowedMimeTypes = $allowedMimeTypes;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return ContactFormManager
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return bool
     */
    public function useRealResponseCode(): bool
    {
        return $this->useRealResponseCode;
    }

    /**
     * @param bool $useRealResponseCode Return a real 400 response if form is not valid.
     * @return ContactFormManager
     */
    public function setUseRealResponseCode(bool $useRealResponseCode): ContactFormManager
    {
        $this->useRealResponseCode = $useRealResponseCode;
        return $this;
    }
}
