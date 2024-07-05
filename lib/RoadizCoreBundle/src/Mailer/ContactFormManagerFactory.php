<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Mailer;

use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Form\Error\FormErrorSerializerInterface;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final class ContactFormManagerFactory
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
        private readonly Environment $templating,
        private readonly MailerInterface $mailer,
        private readonly Settings $settingsBag,
        private readonly DocumentUrlGeneratorInterface $documentUrlGenerator,
        private readonly FormFactoryInterface $formFactory,
        private readonly FormErrorSerializerInterface $formErrorSerializer,
        private readonly ?string $recaptchaPrivateKey,
        private readonly ?string $recaptchaPublicKey
    ) {
    }

    public function create(): ContactFormManager
    {
        return new ContactFormManager(
            $this->requestStack,
            $this->translator,
            $this->templating,
            $this->mailer,
            $this->settingsBag,
            $this->documentUrlGenerator,
            $this->formFactory,
            $this->formErrorSerializer,
            $this->recaptchaPrivateKey,
            $this->recaptchaPublicKey
        );
    }
}
