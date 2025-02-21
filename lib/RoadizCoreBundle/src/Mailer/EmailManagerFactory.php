<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Mailer;

use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final readonly class EmailManagerFactory
{
    public function __construct(
        private RequestStack $requestStack,
        private TranslatorInterface $translator,
        private Environment $templating,
        private MailerInterface $mailer,
        private Settings $settingsBag,
        private DocumentUrlGeneratorInterface $documentUrlGenerator,
        private bool $useReplyTo = true,
    ) {
    }

    public function create(): EmailManager
    {
        return new EmailManager(
            $this->requestStack,
            $this->translator,
            $this->templating,
            $this->mailer,
            $this->settingsBag,
            $this->documentUrlGenerator,
            $this->useReplyTo,
        );
    }
}
