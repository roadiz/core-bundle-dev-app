<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use RZ\Roadiz\CoreBundle\Controller\RedirectionController;
use Symfony\Component\Routing\Matcher\RedirectableUrlMatcher as BaseMatcher;

final class RedirectableUrlMatcher extends BaseMatcher
{
    /**
     * Redirects the user to another URL.
     *
     * @param string      $path   The path info to redirect to
     * @param string      $route  The route that matched
     * @param string|null $scheme The URL scheme (null to keep the current one)
     *
     * @return array An array of parameters
     */
    public function redirect(string $path, string $route, ?string $scheme = null): array
    {
        return [
            '_controller' => RedirectionController::class.'::redirectToRouteAction',
            'path' => $path,
            'permanent' => true,
            'scheme' => $scheme,
            'httpPort' => $this->context->getHttpPort(),
            'httpsPort' => $this->context->getHttpsPort(),
            '_route' => $route,
        ];
    }
}
