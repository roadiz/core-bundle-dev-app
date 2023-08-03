<?php

declare(strict_types=1);

namespace RZ\Roadiz\CompatBundle\EventSubscriber;

use RZ\Roadiz\CompatBundle\Controller\AppController;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Entity\Theme;
use RZ\Roadiz\CoreBundle\Exception\MaintenanceModeException;
use RZ\Roadiz\CompatBundle\Theme\ThemeResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

final class MaintenanceModeSubscriber implements EventSubscriberInterface
{
    private Settings $settings;
    private Security $security;
    private ThemeResolverInterface $themeResolver;
    private ContainerInterface $serviceLocator;

    public function __construct(
        Settings $settings,
        Security $security,
        ThemeResolverInterface $themeResolver,
        ContainerInterface $serviceLocator
    ) {
        $this->settings = $settings;
        $this->security = $security;
        $this->themeResolver = $themeResolver;
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @return array
     */
    private function getAuthorizedRoutes(): array
    {
        return [
            'loginPage',
            'loginRequestPage',
            'loginRequestConfirmPage',
            'loginResetConfirmPage',
            'loginResetPage',
            'loginFailedPage',
            'loginCheckPage',
            'logoutPage',
            'FontFile',
            'FontFaceCSS',
            'loginImagePage',
            'interventionRequestProcess',
            '_profiler_home',
            '_profiler_search',
            '_profiler_search_bar',
            '_profiler_phpinfo',
            '_profiler_search_results',
            '_profiler_open_file',
            '_profiler',
            '_profiler_router',
            '_profiler_exception',
            '_profiler_exception_css',
            '_wdt',
        ];
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest'],
        ];
    }

    /**
     * @param RequestEvent $event
     * @throws MaintenanceModeException
     */
    public function onRequest(RequestEvent $event): void
    {
        if ($event->isMainRequest()) {
            if (\in_array($event->getRequest()->get('_route'), $this->getAuthorizedRoutes())) {
                return;
            }

            $maintenanceMode = (bool) $this->settings->get('maintenance_mode', false);
            if (
                $maintenanceMode === true &&
                !$this->security->isGranted('ROLE_BACKEND_USER')
            ) {
                $theme = $this->themeResolver->findTheme(null);
                if (null !== $theme) {
                    throw new MaintenanceModeException($this->getControllerForTheme($theme, $event->getRequest()));
                }
                throw new MaintenanceModeException();
            }
        }
    }

    /**
     * @param Theme   $theme
     * @param Request $request
     *
     * @return AbstractController
     */
    private function getControllerForTheme(Theme $theme, Request $request): AbstractController
    {
        $ctrlClass = $theme->getClassName();
        $controller = new $ctrlClass();
        $serviceId = get_class($controller);

        if ($this->serviceLocator->has($serviceId)) {
            $controller = $this->serviceLocator->get($serviceId);
        }

        if (!$controller instanceof AbstractController) {
            throw new \RuntimeException(sprintf(
                'Theme controller %s must extend %s class',
                $ctrlClass,
                AbstractController::class
            ));
        }

        if ($controller instanceof AppController) {
            $controller->prepareBaseAssignation();
            // No node controller matching in install mode
            $request->attributes->set('theme', $controller->getTheme());
        }

        /*
         * Set request locale if _locale param
         * is present in Route.
         */
        $routeParams = $request->get('_route_params');
        if (!empty($routeParams["_locale"])) {
            $request->setLocale($routeParams["_locale"]);
        }

        return $controller;
    }
}
