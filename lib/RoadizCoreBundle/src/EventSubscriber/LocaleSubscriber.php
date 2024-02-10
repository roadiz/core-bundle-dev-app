<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RequestContextAwareInterface;

final class LocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(
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
            RequestEvent::class => [['onKernelRequest', 16]],
        ];
    }

    private function getDefaultTranslation(): ?TranslationInterface
    {
        return $this->managerRegistry->getRepository(Translation::class)->findDefault();
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $locale = $request->query->get('_locale') ?? $request->attributes->get('_locale');

        /*
         * Set default locale
         */
        if (null !== $locale && $locale !== '') {
            $this->setLocale($event, $locale);
            return;
        }

        if (!$request->attributes->getBoolean('_stateless') && $request->hasPreviousSession()) {
            $locale = $request->getSession()->get('_locale', null);
            if (null !== $locale) {
                $this->setLocale($event, $locale);
                return;
            }
        }

        if (null !== $translation = $this->getDefaultTranslation()) {
            $shortLocale = $translation->getLocale();
            $this->setLocale($event, $shortLocale);
            return;
        }
    }

    private function setLocale(RequestEvent $event, string $locale): void
    {
        $event->getRequest()->setLocale($locale);
        \Locale::setDefault($locale);
        $this->router->getContext()->setParameter('_locale', $locale);
    }
}
