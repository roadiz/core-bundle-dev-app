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

final readonly class ContactFormManagerFactory
{
    public function __construct(
        private RequestStack $requestStack,
        private TranslatorInterface $translator,
        private Environment $templating,
        private MailerInterface $mailer,
        private Settings $settingsBag,
        private DocumentUrlGeneratorInterface $documentUrlGenerator,
        private FormFactoryInterface $formFactory,
        private FormErrorSerializerInterface $formErrorSerializer,
        private ?string $recaptchaPrivateKey,
        private ?string $recaptchaPublicKey,
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
