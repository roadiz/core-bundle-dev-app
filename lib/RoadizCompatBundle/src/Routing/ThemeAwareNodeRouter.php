<?php

declare(strict_types=1);

namespace RZ\Roadiz\CompatBundle\Routing;

use Psr\Cache\InvalidArgumentException;
use RZ\Roadiz\CompatBundle\Theme\ThemeResolverInterface;
use RZ\Roadiz\CoreBundle\Routing\NodeRouter;
use Symfony\Cmf\Component\Routing\VersatileGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

final readonly class ThemeAwareNodeRouter implements RouterInterface, RequestMatcherInterface, VersatileGeneratorInterface
{
    public function __construct(
        private ThemeResolverInterface $themeResolver,
        private NodeRouter $innerRouter,
    ) {
    }

    public function setContext(RequestContext $context): void
    {
        $this->innerRouter->setContext($context);
    }

    public function getContext(): RequestContext
    {
        return $this->innerRouter->getContext();
    }

    public function matchRequest(Request $request): array
    {
        return $this->innerRouter->matchRequest($request);
    }

    public function getRouteCollection(): RouteCollection
    {
        return $this->innerRouter->getRouteCollection();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        $this->innerRouter->setTheme($this->themeResolver->findTheme($this->getContext()->getHost()));

        return $this->innerRouter->generate($name, $parameters, $referenceType);
    }

    public function match(string $pathinfo): array
    {
        return $this->innerRouter->match($pathinfo);
    }

    public function getRouteDebugMessage(mixed $name, array $parameters = []): string
    {
        return $this->innerRouter->getRouteDebugMessage($name, $parameters);
    }
}
