<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RequestContextAwareInterface;

final class LocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly PreviewResolverInterface $previewResolver,
        private readonly ManagerRegistry $managerRegistry,
        private readonly RequestContextAwareInterface $router
    ) {
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // must be registered just after Symfony\Component\HttpKernel\EventListener\LocaleListener
            RequestEvent::class => ['onKernelRequest', 16],
        ];
    }

    private function getRepository(): TranslationRepository
    {
        return $this->managerRegistry->getRepository(Translation::class);
    }

    private function getDefaultTranslation(): ?TranslationInterface
    {
        return $this->getRepository()->findDefault();
    }

    private function supportsLocale(?string $locale): bool
    {
        if (null === $locale || $locale === '') {
            return false;
        }

        if ($this->previewResolver->isPreview()) {
            $locales = $this->getRepository()->getAllLocales();
        } else {
            $locales = $this->getRepository()->getAvailableLocales();
        }
        return \in_array(
            $locale,
            $locales,
            true
        );
    }

    private function getTranslationByLocale(string $locale): ?TranslationInterface
    {
        if ($this->previewResolver->isPreview()) {
            return $this->getRepository()->findOneByLocaleOrOverrideLocale($locale);
        }
        return $this->getRepository()->findOneAvailableByLocaleOrOverrideLocale($locale);
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $locale = $request->query->get('_locale') ?? $request->attributes->get('_locale');

        /*
         * Set default locale
         */
        if ($this->supportsLocale($locale)) {
            $this->setTranslation($request, $this->getTranslationByLocale($locale));
            return;
        }

        $statelessRoutes = [
            'api_genid',
            'api_doc',
            'api_entrypoint',
            'api_graphql_entrypoint',
            'api_jsonld_context',
            'healthCheckAction',
            'interventionRequestProcess',
        ];
        if (
            !\in_array($request->attributes->getString('_route'), $statelessRoutes, true) &&
            !$request->attributes->getBoolean('_stateless') &&
            $request->hasPreviousSession()
        ) {
            $sessionLocale = $request->getSession()->get('_locale', null);
            if ($this->supportsLocale($sessionLocale)) {
                $this->setTranslation($request, $this->getTranslationByLocale($sessionLocale));
                return;
            }
        }

        if (null !== $translation = $this->getDefaultTranslation()) {
            $this->setTranslation($request, $translation);
            return;
        }
    }

    private function setTranslation(Request $request, ?TranslationInterface $translation): void
    {
        if (null === $translation) {
            return;
        }
        $locale = $translation->getPreferredLocale();
        /*
         * Set current translation globally for controllers, utils, etc
         */
        $request->attributes->set('_translation', $translation);
        $request->attributes->set('_locale', $locale);
        $request->setLocale($locale);
        \Locale::setDefault($locale);
        $this->router->getContext()->setParameter('_locale', $locale);
    }
}
