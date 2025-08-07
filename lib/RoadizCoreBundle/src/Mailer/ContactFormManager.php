<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Mailer;

use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Captcha\CaptchaServiceInterface;
use RZ\Roadiz\CoreBundle\Exception\BadFormRequestException;
use RZ\Roadiz\CoreBundle\Form\CaptchaType;
use RZ\Roadiz\CoreBundle\Form\Error\FormErrorSerializerInterface;
use RZ\Roadiz\CoreBundle\Form\HoneypotType;
use RZ\Roadiz\CoreBundle\Notifier\ContactFormNotification;
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
use Symfony\Component\Mime\Address;
use Symfony\Component\Notifier\Notifier;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\RecipientInterface;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal use ContactFormManagerFactory to create a new instance
 */
final class ContactFormManager
{
    private string $formName = 'contact_form';
    private ?string $emailTitle = null;
    private ?string $subject = null;
    private string $emailType = 'contact.form';
    /**
     * @var array<RecipientInterface>|null
     */
    private ?array $recipients = null;
    private ?string $redirectUrl = null;
    private ?FormBuilderInterface $formBuilder = null;
    private ?FormInterface $form = null;
    private array $options = [];
    private string $method = Request::METHOD_POST;
    private bool $emailStrictMode = false;
    private bool $useRealResponseCode = false;
    private array $allowedMimeTypes = [
        'application/pdf',
        'application/x-pdf',
        'image/jpeg',
        'image/png',
        'image/gif',
    ];
    private int $maxFileSize = 5242880; // 5MB

    /*
     * DO NOT DIRECTLY USE THIS CONSTRUCTOR
     * USE 'ContactFormManagerFactory' Factory Service
     */
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
        private readonly Settings $settingsBag,
        private readonly NotifierInterface $notifier,
        private readonly FormFactoryInterface $formFactory,
        private readonly FormErrorSerializerInterface $formErrorSerializer,
        private readonly CaptchaServiceInterface $captchaService,
    ) {
        $this->options = [
            'attr' => [
                'id' => 'contactForm',
            ],
        ];
    }

    public function getFormName(): string
    {
        return $this->formName;
    }

    /**
     * Use this method BEFORE withDefaultFields().
     */
    public function setFormName(string $formName): ContactFormManager
    {
        $this->formName = $formName;

        return $this;
    }

    /**
     * Use this method BEFORE withDefaultFields().
     *
     * @return $this
     */
    public function disableCsrfProtection(): self
    {
        $this->options['csrf_protection'] = false;

        return $this;
    }

    public function getForm(): FormInterface
    {
        return $this->form;
    }

    /**
     * Using the strict mode requires the "egulias/email-validator" library.
     *
     * Use this method BEFORE withDefaultFields().
     *
     * @see https://symfony.com/doc/4.4/reference/constraints/Email.html#strict
     *
     * @return $this
     */
    public function setEmailStrictMode(bool $emailStrictMode = true): self
    {
        $this->emailStrictMode = $emailStrictMode;

        return $this;
    }

    public function isEmailStrictMode(): bool
    {
        return $this->emailStrictMode;
    }

    /**
     * Adds email, name and message fields with their constraints.
     *
     * @return $this
     */
    public function withDefaultFields(bool $useHoneypot = true): self
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
                        Email::VALIDATION_MODE_HTML5,
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
     * Use this method AFTER withDefaultFields().
     *
     * @return $this
     */
    public function withHoneypot(string $honeypotName = 'eml'): self
    {
        $this->getFormBuilder()->add($honeypotName, HoneypotType::class);

        return $this;
    }

    /**
     * Use this method AFTER withDefaultFields().
     *
     * @return $this
     */
    public function withUserConsent(string $consentDescription = 'contact_form.user_consent'): self
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

    public function getFormBuilder(): FormBuilderInterface
    {
        if (null === $this->formBuilder) {
            $this->formBuilder = $this->formFactory
                ->createNamedBuilder($this->getFormName(), FormType::class, null, $this->options)
                ->setMethod($this->method);
        }

        return $this->formBuilder;
    }

    public function withCaptcha(): self
    {
        if ($this->captchaService->isEnabled()) {
            $this->getFormBuilder()->add($this->captchaService->getFieldName(), CaptchaType::class);
        }

        return $this;
    }

    /**
     * Handle custom form validation and send it as an email.
     *
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
        $returnJson = $request->isXmlHttpRequest()
            || 'json' === $request->getRequestFormat()
            || (1 === count($request->getAcceptableContentTypes()) && 'application/json' === $request->getAcceptableContentTypes()[0])
            || ($request->attributes->has('_format') && 'json' === $request->attributes->get('_format'));

        if ($this->form->isSubmitted()) {
            if ($this->form->isValid()) {
                try {
                    if (null !== $onValid) {
                        $onValid($this->form);
                    }

                    $uploadedFiles = $this->handleFiles();
                    $this->notifier->send(
                        $this->createNotificationFromForm($this->form, $uploadedFiles),
                        ...$this->getRecipients(),
                    );
                    if ($returnJson) {
                        return new JsonResponse([], Response::HTTP_ACCEPTED);
                    } else {
                        if ($request->hasPreviousSession()) {
                            /** @var Session $session */
                            $session = $request->getSession();
                            $session->getFlashBag()
                                ->add('confirm', $this->translator->trans('form.successfully.sent'));
                        }

                        $this->redirectUrl ??= $request->getUri();

                        return new RedirectResponse($this->redirectUrl);
                    }
                } catch (BadFormRequestException $e) {
                    if (null !== $e->getFieldErrored() && $this->form->has($e->getFieldErrored())) {
                        $this->form->get($e->getFieldErrored())->addError(new FormError($e->getMessage()));
                    } else {
                        $this->form->addError(new FormError($e->getMessage()));
                    }
                } catch (TransportExceptionInterface) {
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
                    'message' => $this->translator->trans('form.has.errors'),
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

    /**
     * @return array<RecipientInterface>
     */
    protected function getRecipients(): array
    {
        if (!empty($this->recipients)) {
            return $this->recipients;
        } elseif ($this->notifier instanceof Notifier) {
            return $this->notifier->getAdminRecipients();
        } else {
            // Fallback to the parent method if Notifier is not used
            return [];
        }
    }

    /**
     * @param array<RecipientInterface>|null $recipients
     */
    public function setRecipients(?array $recipients): ContactFormManager
    {
        if (null !== $recipients) {
            foreach ($recipients as $recipient) {
                if (!$recipient instanceof RecipientInterface) {
                    throw new \InvalidArgumentException('All recipients must implement RecipientInterface.');
                }
            }
        }
        $this->recipients = $recipients;

        return $this;
    }

    /**
     * @return array<string, UploadedFile>
     *
     * @throws BadFormRequestException
     */
    protected function handleFiles(): array
    {
        $uploadedFiles = [];
        $request = $this->requestStack->getMainRequest();
        if (null === $request) {
            return [];
        }
        /*
         * Files values
         */
        foreach ($request->files as $files) {
            /**
             * @var string             $name
             * @var UploadedFile|array $uploadedFile
             */
            foreach ($files as $name => $uploadedFile) {
                if (null !== $uploadedFile) {
                    if (is_array($uploadedFile)) {
                        /**
                         * @var string             $singleName
                         * @var UploadedFile|array $singleUploadedFile
                         */
                        foreach ($uploadedFile as $singleName => $singleUploadedFile) {
                            if (is_array($singleUploadedFile)) {
                                /**
                                 * @var string       $singleName2
                                 * @var UploadedFile $singleUploadedFile2
                                 */
                                foreach ($singleUploadedFile as $singleName2 => $singleUploadedFile2) {
                                    $this->addUploadedFile($uploadedFiles, $singleName2, $singleUploadedFile2);
                                }
                            } else {
                                $this->addUploadedFile($uploadedFiles, $singleName, $singleUploadedFile);
                            }
                        }
                    } else {
                        $this->addUploadedFile($uploadedFiles, $name, $uploadedFile);
                    }
                }
            }
        }

        return $uploadedFiles;
    }

    /**
     * @return $this
     *
     * @throws BadFormRequestException
     */
    protected function addUploadedFile(array &$uploadedFiles, string $name, UploadedFile $uploadedFile): ContactFormManager
    {
        if (
            !$uploadedFile->isValid()
            || !in_array($uploadedFile->getMimeType(), $this->allowedMimeTypes)
            || $uploadedFile->getSize() > $this->maxFileSize
        ) {
            throw new BadFormRequestException($this->translator->trans('file.not.accepted'), Response::HTTP_FORBIDDEN, 'danger', $name);
        } else {
            $uploadedFiles[$name] = $uploadedFile;
        }

        return $this;
    }

    protected function findEmailData(array $formData): ?string
    {
        foreach ($formData as $key => $value) {
            if (
                (new UnicodeString($key))->containsAny('email')
                && is_string($value)
                && filter_var($value, FILTER_VALIDATE_EMAIL)
            ) {
                return $value;
            } elseif (is_array($value) && null !== $email = $this->findEmailData($value)) {
                return $email;
            }
        }

        return null;
    }

    /**
     * @param array<string, UploadedFile> $uploadedFiles
     *
     * @throws \Exception
     */
    protected function createNotificationFromForm(FormInterface $form, array $uploadedFiles): ContactFormNotification
    {
        $formData = $form->getData();
        $fields = $this->flattenFormData($form, []);

        /*
         * Sender email
         */
        $emailData = $this->findEmailData($formData);

        /**
         * @var string       $key
         * @var UploadedFile $uploadedFile
         */
        foreach ($uploadedFiles as $key => $uploadedFile) {
            $fields[] = [
                'name' => strip_tags((string) $key),
                'value' => (strip_tags($uploadedFile->getClientOriginalName()).
                    ' ['.$uploadedFile->guessExtension().']'),
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

        return new ContactFormNotification(
            [
                'emailType' => $this->getEmailType(),
                'title' => $this->getEmailTitle(),
                'fields' => $fields,
            ],
            $this->requestStack->getMainRequest()->getLocale(),
            $uploadedFiles,
            $emailData ? new Address($emailData) : null,
            $this->getSubject(),
            ['email']
        );
    }

    public function getSubject(): string
    {
        return $this->subject ?? $this->translator->trans(
            'new.contact.form.%site%',
            ['%site%' => $this->settingsBag->get('site_name')]
        );
    }

    public function getEmailTitle(): ?string
    {
        return $this->emailTitle ?? $this->getSubject();
    }

    public function getEmailType(): string
    {
        return $this->emailType;
    }

    public function setEmailTitle(?string $emailTitle): ContactFormManager
    {
        $this->emailTitle = $emailTitle;

        return $this;
    }

    public function setSubject(?string $subject): ContactFormManager
    {
        $this->subject = $subject;

        return $this;
    }

    public function setEmailType(string $emailType): ContactFormManager
    {
        $this->emailType = $emailType;

        return $this;
    }

    protected function isFieldPrivate(FormInterface $form): bool
    {
        $key = $form->getName();
        $privateFieldNames = [
            $this->captchaService->getFieldName(),
        ];

        return
            is_string($key)
            && ('_' === \mb_substr($key, 0, 1) || \in_array($key, $privateFieldNames))
        ;
    }

    protected function flattenFormData(FormInterface $form, array $fields): array
    {
        /** @var FormInterface $formItem */
        foreach ($form as $formItem) {
            $key = $formItem->getName();
            $value = $formItem->getData();
            $displayName = $formItem->getConfig()->getOption('label') ??
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
     * Gets the value of redirectUrl.
     */
    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    /**
     * Sets the value of redirectUrl.
     *
     * @param string|null $redirectUrl Redirect url
     */
    public function setRedirectUrl(?string $redirectUrl): self
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    /**
     * Gets the value of maxFileSize.
     */
    public function getMaxFileSize(): int
    {
        return $this->maxFileSize;
    }

    /**
     * Sets the value of maxFileSize.
     *
     * @param int $maxFileSize the max file size
     */
    public function setMaxFileSize($maxFileSize): self
    {
        $this->maxFileSize = (int) $maxFileSize;

        return $this;
    }

    /**
     * Gets the value of allowedMimeTypes.
     */
    public function getAllowedMimeTypes(): array
    {
        return $this->allowedMimeTypes;
    }

    /**
     * Sets the value of allowedMimeTypes.
     *
     * @param array $allowedMimeTypes the allowed mime types
     */
    public function setAllowedMimeTypes(array $allowedMimeTypes): self
    {
        $this->allowedMimeTypes = $allowedMimeTypes;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return $this
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function useRealResponseCode(): bool
    {
        return $this->useRealResponseCode;
    }

    /**
     * @param bool $useRealResponseCode return a real 400 response if form is not valid
     */
    public function setUseRealResponseCode(bool $useRealResponseCode): ContactFormManager
    {
        $this->useRealResponseCode = $useRealResponseCode;

        return $this;
    }
}
