<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Mailer;

use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * @internal use EmailManagerFactory to create a new instance
 *
 * @deprecated since 2.6, use symfony/notifier instead with custom EmailNotification
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
    protected array $assignation;
    /** @var File[] */
    protected array $files = [];
    protected array $resources = [];

    /*
     * DO NOT DIRECTLY USE THIS CONSTRUCTOR
     * USE 'EmailManagerFactory' Factory Service
     *
     * @internal
     */
    public function __construct(
        protected readonly RequestStack $requestStack,
        protected readonly TranslatorInterface $translator,
        protected readonly MailerInterface $mailer,
        protected readonly Settings $settingsBag,
        protected readonly DocumentUrlGeneratorInterface $documentUrlGenerator,
        protected readonly bool $useReplyTo = true,
    ) {
        $this->assignation = [];
    }

    public function getSupportEmailAddress(): ?string
    {
        $supportEmail = $this->settingsBag->get('support_email_address', null);
        if (false !== filter_var($supportEmail, FILTER_VALIDATE_EMAIL)) {
            return $supportEmail;
        }

        return null;
    }

    public function createMessage(): TemplatedEmail
    {
        $email = (new TemplatedEmail())
            ->subject($this->getSubject() ?? 'No subject')
            ->to(...($this->getReceiver() ?? []))
            ->context($this->assignation)
        ;

        if (null !== $this->emailTemplate) {
            $email->htmlTemplate($this->emailTemplate);
        }
        if (null !== $this->emailPlainTextTemplate) {
            $email->textTemplate($this->emailPlainTextTemplate);
        }

        /*
         * Use sender email in ReplyTo: header only
         * to keep From: header with a know domain email.
         */
        if (null !== $this->getSender() && null !== $this->getSenderEmail() && $this->useReplyTo) {
            $email
                // Force using string and only one email
                ->returnPath($this->getSenderEmail())
                ->replyTo(...$this->getSender());
        }

        return $email;
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

        $email = $this->createMessage();

        /*
         * File attachment requires local file storage.
         */
        foreach ($this->files as $file) {
            $email->attachFromPath($file->getRealPath(), $file->getFilename());
        }
        foreach ($this->resources as $resourceArray) {
            [$resource, $filename, $mimeType] = $resourceArray;
            $email->attach($resource, $filename, $mimeType);
        }

        // Send the message
        $this->mailer->send($email);
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

    /**
     * @return $this
     */
    public function setEmailTemplate(?string $emailTemplate = null): static
    {
        $this->emailTemplate = $emailTemplate;

        return $this;
    }

    /**
     * @return $this
     */
    public function setEmailPlainTextTemplate(?string $emailPlainTextTemplate = null): static
    {
        $this->emailPlainTextTemplate = $emailPlainTextTemplate;

        return $this;
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
