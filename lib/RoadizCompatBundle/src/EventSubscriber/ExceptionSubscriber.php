<?php

declare(strict_types=1);

namespace RZ\Roadiz\CompatBundle\EventSubscriber;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RZ\Roadiz\CompatBundle\Controller\AppController;
use RZ\Roadiz\CompatBundle\Theme\ThemeResolverInterface;
use RZ\Roadiz\CoreBundle\Entity\Theme;
use RZ\Roadiz\CoreBundle\Exception\MaintenanceModeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

final readonly class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ThemeResolverInterface $themeResolver,
        private ContainerInterface $serviceLocator,
        private bool $debug,
    ) {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        /*
         * Roadiz exception handling must be triggered AFTER firewall exceptions
         */
        return [
            KernelEvents::EXCEPTION => ['onKernelException', -1],
        ];
    }

    private function isFormatJson(Request $request): bool
    {
        if (
            $request->attributes->has('_format')
            && (
                'json' == $request->attributes->get('_format')
                || 'ld+json' == $request->attributes->get('_format')
            )
        ) {
            return true;
        }

        $contentType = $request->headers->get('Content-Type');
        if (
            \is_string($contentType)
            && (
                \str_starts_with($contentType, 'application/json')
                || \str_starts_with($contentType, 'application/ld+json')
            )
        ) {
            return true;
        }

        if (
            in_array('application/json', $request->getAcceptableContentTypes())
            || in_array('application/ld+json', $request->getAcceptableContentTypes())
        ) {
            return true;
        }

        return false;
    }

    private function getHttpStatusCode(\Throwable $exception): int
    {
        if ($exception instanceof AccessDeniedException || $exception instanceof AccessDeniedHttpException) {
            return Response::HTTP_FORBIDDEN;
        } elseif ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        } elseif ($exception instanceof ResourceNotFoundException) {
            return Response::HTTP_NOT_FOUND;
        } elseif ($exception instanceof MaintenanceModeException) {
            return Response::HTTP_SERVICE_UNAVAILABLE;
        }

        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Throwable
     * @throws SyntaxError
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

        if (!$this->isFormatJson($event->getRequest())) {
            if ($exception instanceof MaintenanceModeException) {
                /*
                 * Themed exception pages…
                 */
                $ctrl = $exception->getController();
                if (
                    null !== $ctrl
                    && method_exists($ctrl, 'maintenanceAction')
                ) {
                    try {
                        /** @var Response $response */
                        $response = $ctrl->maintenanceAction($event->getRequest());
                        // Set http code according to status
                        $response->setStatusCode($this->getHttpStatusCode($exception));
                        $event->setResponse($response);

                        return;
                    } catch (LoaderError) {
                        // Twig template does not exist
                    }
                }
            }
            if (null !== $theme = $this->isNotFoundExceptionWithTheme($event)) {
                $event->setResponse($this->createThemeNotFoundResponse($theme, $exception, $event));
            }
        }
    }

    protected function isNotFoundExceptionWithTheme(ExceptionEvent $event): ?Theme
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        if (
            $exception instanceof ResourceNotFoundException
            || $exception instanceof NotFoundHttpException
            || (
                null !== $exception->getPrevious()
                && (
                    $exception->getPrevious() instanceof ResourceNotFoundException
                    || $exception->getPrevious() instanceof NotFoundHttpException
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
     * @throws LoaderError
     * @throws RuntimeError
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Throwable
     * @throws SyntaxError
     */
    protected function createThemeNotFoundResponse(Theme $theme, \Throwable $exception, ExceptionEvent $event): Response
    {
        $ctrlClass = $theme->getClassName();
        $controller = new $ctrlClass();
        $serviceId = $controller::class;

        if ($this->serviceLocator->has($serviceId)) {
            $controller = $this->serviceLocator->get($serviceId);
        }
        if ($controller instanceof AppController) {
            return $controller->throw404($exception->getMessage());
        }

        throw $exception;
    }
}
