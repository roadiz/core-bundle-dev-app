<?php

declare(strict_types=1);

namespace RZ\Roadiz\CompatBundle\EventSubscriber;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CompatBundle\Controller\AppController;
use RZ\Roadiz\CompatBundle\Theme\ThemeResolverInterface;
use RZ\Roadiz\CoreBundle\Entity\Theme;
use RZ\Roadiz\CoreBundle\Exception\ExceptionViewer;
use RZ\Roadiz\CoreBundle\Exception\MaintenanceModeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;

/**
 * @package RZ\Roadiz\CoreBundle\Event
 */
final class ExceptionSubscriber implements EventSubscriberInterface
{
    protected LoggerInterface $logger;
    protected bool $debug;
    protected ExceptionViewer $viewer;
    private ThemeResolverInterface $themeResolver;
    private ContainerInterface $serviceLocator;

    public function __construct(
        ThemeResolverInterface $themeResolver,
        ExceptionViewer $viewer,
        ContainerInterface $serviceLocator,
        LoggerInterface $logger,
        bool $debug
    ) {
        $this->debug = $debug;
        $this->viewer = $viewer;
        $this->themeResolver = $themeResolver;
        $this->serviceLocator = $serviceLocator;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        /*
         * Roadiz exception handling must be triggered AFTER firewall exceptions
         */
        return [
            KernelEvents::EXCEPTION => ['onKernelException', -1],
        ];
    }

    /**
     * @param ExceptionEvent $event
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        if ($this->debug) {
            return;
        }

        // You get the exception object from the received event
        $exception = $event->getThrowable();

        /*
         * Get previous exception if thrown in Twig execution context.
         */
        if ($exception instanceof RuntimeError && null !== $exception->getPrevious()) {
            $exception = $exception->getPrevious();
        }

        if (!$this->viewer->isFormatJson($event->getRequest())) {
            if ($exception instanceof MaintenanceModeException) {
                /*
                 * Themed exception pages…
                 */
                $ctrl = $exception->getController();
                if (
                    null !== $ctrl &&
                    method_exists($ctrl, 'maintenanceAction')
                ) {
                    try {
                        /** @var Response $response */
                        $response = $ctrl->maintenanceAction($event->getRequest());
                        // Set http code according to status
                        $response->setStatusCode($this->viewer->getHttpStatusCode($exception));
                        $event->setResponse($response);
                        return;
                    } catch (LoaderError $error) {
                        // Twig template does not exist
                    }
                }
            }
            if (null !== $theme = $this->isNotFoundExceptionWithTheme($event)) {
                $event->setResponse($this->createThemeNotFoundResponse($theme, $exception, $event));
            }
        }
    }

    /**
     * Create an emergency response to be sent instead of error logs.
     *
     * @param \Exception|\TypeError $e
     * @param Request $request
     *
     * @return Response
     */
    protected function getEmergencyResponse($e, Request $request): Response
    {
        /*
         * Log error before displaying a fallback page.
         */
        $class = get_class($e);
        /*
         * Do not flood logs with not-found errors
         */
        if (!($e instanceof NotFoundHttpException) && !($e instanceof ResourceNotFoundException)) {
            if ($e instanceof HttpExceptionInterface) {
                // If HTTP exception do not log to critical
                $this->logger->notice($e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'exception' => $class,
                ]);
            } else {
                $this->logger->emergency($e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'exception' => $class,
                ]);
            }
        }

        return $this->viewer->getResponse($e, $request, $this->debug);
    }

    /**
     * @param ExceptionEvent $event
     * @return null|Theme
     */
    protected function isNotFoundExceptionWithTheme(ExceptionEvent $event): ?Theme
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        if (
            $exception instanceof ResourceNotFoundException ||
            $exception instanceof NotFoundHttpException ||
            (
                null !== $exception->getPrevious() &&
                (
                    $exception->getPrevious() instanceof ResourceNotFoundException ||
                    $exception->getPrevious() instanceof NotFoundHttpException
                )
            )
        ) {
            if (null !== $theme = $this->themeResolver->findTheme($request->getHost())) {
                /*
                 * 404 page
                 */
                $request->attributes->set('theme', $theme);

                return $theme;
            }
        }

        return null;
    }

    /**
     * @param Theme $theme
     * @param \Throwable $exception
     * @param ExceptionEvent $event
     *
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Throwable
     * @throws \Twig\Error\SyntaxError
     */
    protected function createThemeNotFoundResponse(Theme $theme, \Throwable $exception, ExceptionEvent $event): Response
    {
        $ctrlClass = $theme->getClassName();
        $controller = new $ctrlClass();
        $serviceId = get_class($controller);

        if ($this->serviceLocator->has($serviceId)) {
            $controller = $this->serviceLocator->get($serviceId);
        }
        if ($controller instanceof AppController) {
            return $controller
                ->prepareBaseAssignation()
                ->throw404($exception->getMessage());
        }

        throw $exception;
    }
}
