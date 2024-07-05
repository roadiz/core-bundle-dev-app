<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Mailer;

use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final class EmailManagerFactory
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
        private readonly Environment $templating,
        private readonly MailerInterface $mailer,
        private readonly Settings $settingsBag,
        private readonly DocumentUrlGeneratorInterface $documentUrlGenerator
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
            $this->documentUrlGenerator
        );
    }
}
