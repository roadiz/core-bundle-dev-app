<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Mailer;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Captcha\CaptchaServiceInterface;
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
        private CaptchaServiceInterface $captchaService,
        private ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        private LoggerInterface $logger,
        private bool $useConstraintViolationList,
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
            $this->captchaService,
            $this->resourceMetadataCollectionFactory,
            $this->logger,
            $this->useConstraintViolationList,
        );
    }
}
