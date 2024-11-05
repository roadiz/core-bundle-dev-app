<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authorization;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

/**
 * This is used by the ExceptionListener to translate an AccessDeniedException
 * to a Response object.
 */
final class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ?LoggerInterface $logger,
        private readonly string $redirectRoute = '',
        private readonly array $redirectParameters = [],
    ) {
    }

    /**
     * Handles access denied failure redirecting to home page.
     *
     * @return Response|null may return null
     */
    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        $this->logger->error('User tried to access: '.$request->getUri());

        $returnJson = $request->isXmlHttpRequest()
            || 'json' === $request->getRequestFormat()
            || (
                1 === count($request->getAcceptableContentTypes())
                && 'application/json' === $request->getAcceptableContentTypes()[0]
            )
            || ($request->attributes->has('_format') && 'json' === $request->attributes->get('_format'));

        if ($returnJson) {
            return new JsonResponse(
                [
                    'message' => $accessDeniedException->getMessage(),
                    'trace' => $accessDeniedException->getTraceAsString(),
                    'exception' => get_class($accessDeniedException),
                ],
                Response::HTTP_FORBIDDEN
            );
        } else {
            if ('' !== $this->redirectRoute) {
                $redirectUrl = $this->urlGenerator->generate($this->redirectRoute, $this->redirectParameters);
            } else {
                $redirectUrl = $request->getBaseUrl();
            }

            // Forbidden code should be set on final response, not the redirection!
            return new RedirectResponse($redirectUrl, Response::HTTP_FOUND);
        }
    }
}
