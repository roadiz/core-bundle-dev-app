<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Mailer;

use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Form\Error\FormErrorSerializerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ContactFormManagerFactory
{
    public function __construct(
        private RequestStack $requestStack,
        private TranslatorInterface $translator,
        private Settings $settingsBag,
        private FormFactoryInterface $formFactory,
        private FormErrorSerializerInterface $formErrorSerializer,
        private NotifierInterface $notifier,
        private ?string $recaptchaPrivateKey,
        private ?string $recaptchaPublicKey,
    ) {
    }

    public function create(): ContactFormManager
    {
        return new ContactFormManager(
            $this->requestStack,
            $this->translator,
            $this->settingsBag,
            $this->notifier,
            $this->formFactory,
            $this->formErrorSerializer,
            $this->recaptchaPrivateKey,
            $this->recaptchaPublicKey
        );
    }
}
