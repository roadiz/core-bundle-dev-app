<?php

declare(strict_types=1);

namespace RZ\Roadiz\CompatBundle\Routing;

use RZ\Roadiz\CompatBundle\Theme\ThemeResolverInterface;
use RZ\Roadiz\CoreBundle\Entity\Theme;
use RZ\Roadiz\CoreBundle\Routing\NodeUrlMatcher;
use RZ\Roadiz\CoreBundle\Routing\NodeUrlMatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;

final readonly class ThemeAwareNodeUrlMatcher implements UrlMatcherInterface, RequestMatcherInterface, NodeUrlMatcherInterface
{
    public function __construct(
        private ThemeResolverInterface $themeResolver,
        private NodeUrlMatcher $innerMatcher,
    ) {
    }

    #[\Override]
    public function match(string $pathinfo): array
    {
        $decodedUrl = rawurldecode($pathinfo);

        /*
         * Try nodes routes
         */
        return $this->matchNode(
            $decodedUrl,
            $this->themeResolver->findTheme($this->getContext()->getHost())
        );
    }

    #[\Override]
    public function setContext(RequestContext $context): void
    {
        $this->innerMatcher->setContext($context);
    }

    #[\Override]
    public function getContext(): RequestContext
    {
        return $this->innerMatcher->getContext();
    }

    #[\Override]
    public function matchRequest(Request $request): array
    {
        return $this->match($request->getPathInfo());
    }

    #[\Override]
    public function getSupportedFormatExtensions(): array
    {
        return $this->innerMatcher->getSupportedFormatExtensions();
    }

    #[\Override]
    public function getDefaultSupportedFormatExtension(): string
    {
        return $this->innerMatcher->getDefaultSupportedFormatExtension();
    }

    #[\Override]
    public function matchNode(string $decodedUrl, ?Theme $theme): array
    {
        return $this->innerMatcher->matchNode(
            $decodedUrl,
            $theme
        );
    }
}
