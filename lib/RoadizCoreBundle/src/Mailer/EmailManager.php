<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Mailer;

use InlineStyle\InlineStyle;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * @internal use EmailManagerFactory to create a new instance
 */
class EmailManager
{
    protected ?string $subject = null;
    protected ?string $emailTitle = null;
    protected ?string $emailType = null;
    /** @var Address[]|null */
    protected ?array $receiver = null;
    /** @var Address[]|null */
    protected ?array $sender = null;
    protected ?Address $origin = null;
    protected string $successMessage = 'email.successfully.sent';
    protected string $failMessage = 'email.has.errors';
    protected ?string $emailTemplate = null;
    protected ?string $emailPlainTextTemplate = null;
    protected string $emailStylesheet;
    protected array $assignation;
    protected ?Email $message;
    /** @var File[] */
    protected array $files = [];
    protected array $resources = [];

    /*
     * DO NOT DIRECTLY USE THIS CONSTRUCTOR
     * USE 'EmailManagerFactory' Factory Service
     */
    public function __construct(
        protected readonly RequestStack $requestStack,
        protected readonly TranslatorInterface $translator,
        protected readonly Environment $templating,
        protected readonly MailerInterface $mailer,
        protected readonly Settings $settingsBag,
        protected readonly DocumentUrlGeneratorInterface $documentUrlGenerator,
    ) {
        $this->assignation = [];
        $this->message = null;
        /*
         * Sets a default CSS for emails.
         */
        $this->emailStylesheet = dirname(__DIR__).'/../css/transactionalStyles.css';
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renderHtmlEmailBody(): string
    {
        return $this->templating->render($this->getEmailTemplate(), $this->assignation);
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renderHtmlEmailBodyWithCss(): string
    {
        if (null !== $this->getEmailStylesheet()) {
            $htmldoc = new InlineStyle($this->renderHtmlEmailBody());
            $css = file_get_contents(
                $this->getEmailStylesheet()
            );
            if (false === $css) {
                throw new \RuntimeException('Unable to read email stylesheet file.');
            }
            $htmldoc->applyStylesheet($css);

            return $htmldoc->getHTML();
        }

        return $this->renderHtmlEmailBody();
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renderPlainTextEmailBody(): string
    {
        return $this->templating->render($this->getEmailPlainTextTemplate(), $this->assignation);
    }

    /**
     * Added headerImageSrc assignation to display email header.
     *
     * @return $this
     */
    public function appendWebsiteIcon(): static
    {
        if (empty($this->assignation['headerImageSrc']) && null !== $this->settingsBag) {
            $adminImage = $this->settingsBag->getDocument('admin_image');
            if ($adminImage instanceof DocumentInterface && null !== $this->documentUrlGenerator) {
                $this->documentUrlGenerator->setDocument($adminImage);
                $this->assignation['headerImageSrc'] = $this->documentUrlGenerator->getUrl(true);
            }
        }

        return $this;
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function createMessage(): Email
    {
        $this->appendWebsiteIcon();

        $this->message = (new Email())
            ->subject($this->getSubject())
            ->from($this->getOrigin())
            ->to(...$this->getReceiver())
            // Force using string and only one email
            ->returnPath($this->getSenderEmail());

        if (null !== $this->getEmailTemplate()) {
            $this->message->html($this->renderHtmlEmailBodyWithCss());
        }
        if (null !== $this->getEmailPlainTextTemplate()) {
            $this->message->text($this->renderPlainTextEmailBody());
        }

        /*
         * Use sender email in ReplyTo: header only
         * to keep From: header with a know domain email.
         */
        if (null !== $this->getSender()) {
            $this->message->replyTo(...$this->getSender());
        }

        return $this->message;
    }

    /**
     * Send email.
     *
     * @throws TransportExceptionInterface
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function send(): void
    {
        if (empty($this->assignation)) {
            throw new \RuntimeException('Can’t send a contact form without data.');
        }

        if (null === $this->message) {
            $this->message = $this->createMessage();
        }

        /*
         * File attachment requires local file storage.
         */
        foreach ($this->files as $file) {
            $this->message->attachFromPath($file->getRealPath(), $file->getFilename());
        }
        foreach ($this->resources as $resourceArray) {
            [$resource, $filename, $mimeType] = $resourceArray;
            $this->message->attach($resource, $filename, $mimeType);
        }

        // Send the message
        $this->mailer->send($this->message);
    }

    public function getSubject(): ?string
    {
        return null !== $this->subject ? trim(strip_tags($this->subject)) : null;
    }

    /**
     * @return $this
     */
    public function setSubject(?string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getEmailTitle(): ?string
    {
        return null !== $this->emailTitle ? trim(strip_tags($this->emailTitle)) : null;
    }

    /**
     * @return $this
     */
    public function setEmailTitle(?string $emailTitle): static
    {
        $this->emailTitle = $emailTitle;

        return $this;
    }

    /**
     * Message destination email(s).
     *
     * @return Address[]|null
     */
    public function getReceiver(): ?array
    {
        return $this->receiver;
    }

    /**
     * Return only one email as string.
     */
    public function getReceiverEmail(): ?string
    {
        if (is_array($this->getReceiver()) && count($this->getReceiver()) > 0) {
            return $this->getReceiver()[0]->getAddress();
        }

        return null;
    }

    /**
     * Sets the value of receiver.
     *
     * @param Address|string|array<string, string>|array<Address> $receiver the receiver
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function setReceiver(mixed $receiver): static
    {
        if ($receiver instanceof Address) {
            $this->receiver = [$receiver];
        } elseif (\is_string($receiver)) {
            $this->receiver = [new Address($receiver)];
        } elseif (\is_array($receiver)) {
            $this->receiver = [];
            foreach ($receiver as $email => $name) {
                if ($name instanceof Address) {
                    $this->receiver[] = $name;
                } elseif (\is_string($email)) {
                    $this->receiver[] = new Address($email, $name);
                } else {
                    $this->receiver[] = new Address($name);
                }
            }
        }

        return $this;
    }

    /**
     * Message virtual sender email.
     *
     * This email will be used as ReplyTo: and ReturnPath:
     *
     * @return Address[]|null
     */
    public function getSender(): ?array
    {
        return $this->sender;
    }

    /**
     * Return only one email as string.
     */
    public function getSenderEmail(): ?string
    {
        if (\is_array($this->sender) && \count($this->sender) > 0) {
            return $this->sender[0]->getAddress();
        }

        return null;
    }

    /**
     * Sets the value of sender.
     *
     * @param Address|string|array<string|int, string>|array<string|int, Address> $sender
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function setSender(mixed $sender): static
    {
        if ($sender instanceof Address) {
            $this->sender = [$sender];
        } elseif (\is_string($sender)) {
            $this->sender = [new Address($sender)];
        } elseif (\is_array($sender)) {
            $this->sender = [];
            foreach ($sender as $email => $name) {
                if ($name instanceof Address) {
                    $this->sender[] = $name;
                } elseif (\is_string($email)) {
                    $this->sender[] = new Address($email, $name);
                } else {
                    $this->sender[] = new Address($name);
                }
            }
        } else {
            throw new \InvalidArgumentException('Sender should be string or array<string>');
        }

        return $this;
    }

    public function getSuccessMessage(): string
    {
        return $this->successMessage;
    }

    /**
     * @return $this
     */
    public function setSuccessMessage(string $successMessage): static
    {
        $this->successMessage = $successMessage;

        return $this;
    }

    public function getFailMessage(): string
    {
        return $this->failMessage;
    }

    /**
     * @return $this
     */
    public function setFailMessage(string $failMessage): static
    {
        $this->failMessage = $failMessage;

        return $this;
    }

    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    public function getTemplating(): Environment
    {
        return $this->templating;
    }

    public function getMailer(): MailerInterface
    {
        return $this->mailer;
    }

    public function getEmailTemplate(): ?string
    {
        return $this->emailTemplate;
    }

    /**
     * @return $this
     */
    public function setEmailTemplate(?string $emailTemplate = null): static
    {
        $this->emailTemplate = $emailTemplate;

        return $this;
    }

    public function getEmailPlainTextTemplate(): ?string
    {
        return $this->emailPlainTextTemplate;
    }

    /**
     * @return $this
     */
    public function setEmailPlainTextTemplate(?string $emailPlainTextTemplate = null): static
    {
        $this->emailPlainTextTemplate = $emailPlainTextTemplate;

        return $this;
    }

    public function getEmailStylesheet(): ?string
    {
        return $this->emailStylesheet;
    }

    /**
     * @return $this
     */
    public function setEmailStylesheet(?string $emailStylesheet = null): static
    {
        $this->emailStylesheet = $emailStylesheet;

        return $this;
    }

    public function getRequest(): Request
    {
        return $this->requestStack->getMainRequest();
    }

    /**
     * Origin is the real From envelop.
     *
     * This must be an email address with a know
     * domain name to be validated on your SMTP server.
     */
    public function getOrigin(): ?Address
    {
        $defaultSender = 'origin@roadiz.io';
        $defaultSenderName = '';
        if (null !== $this->settingsBag && $this->settingsBag->get('email_sender')) {
            $defaultSender = $this->settingsBag->get('email_sender');
            $defaultSenderName = $this->settingsBag->get('site_name', '') ?? '';
        }

        return $this->origin ?? new Address($defaultSender, $defaultSenderName);
    }

    /**
     * @return $this
     */
    public function setOrigin(string $origin): static
    {
        $this->origin = new Address($origin);

        return $this;
    }

    public function getAssignation(): array
    {
        return $this->assignation;
    }

    /**
     * @return $this
     */
    public function setAssignation(array $assignation): static
    {
        $this->assignation = $assignation;

        return $this;
    }

    public function getEmailType(): ?string
    {
        return $this->emailType;
    }

    /**
     * @return $this
     */
    public function setEmailType(?string $emailType): static
    {
        $this->emailType = $emailType;

        return $this;
    }

    /**
     * @return File[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @param File[] $files
     *
     * @return $this
     */
    public function setFiles(array $files): static
    {
        $this->files = $files;

        return $this;
    }

    /**
     * @return array [$resource, $filename, $mimeType]
     */
    public function getResources(): array
    {
        return $this->resources;
    }

    /**
     * @param resource $resource
     *
     * @return $this
     */
    public function addResource($resource, string $filename, string $mimeType): static
    {
        $this->resources[] = [$resource, $filename, $mimeType];

        return $this;
    }
}
