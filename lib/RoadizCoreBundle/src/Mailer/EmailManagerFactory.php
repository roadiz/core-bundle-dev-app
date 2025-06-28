<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Mailer;

use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @deprecated since 2.6, use symfony/notifier instead with custom EmailNotification
 */
final readonly class EmailManagerFactory
{
    public function __construct(
        private RequestStack $requestStack,
        private TranslatorInterface $translator,
        private MailerInterface $mailer,
        private Settings $settingsBag,
        private DocumentUrlGeneratorInterface $documentUrlGenerator,
        private bool $useReplyTo = true,
    ) {
    }

    /**
     * @deprecated since 2.6, use symfony/notifier instead with custom EmailNotification
     */
    public function create(): EmailManager
    {
        return new EmailManager(
            $this->requestStack,
            $this->translator,
            $this->mailer,
            $this->settingsBag,
            $this->documentUrlGenerator,
            $this->useReplyTo,
        );
    }
}
