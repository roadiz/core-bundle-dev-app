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

final class ThemeAwareNodeUrlMatcher implements UrlMatcherInterface, RequestMatcherInterface, NodeUrlMatcherInterface
{
    public function __construct(
        private readonly ThemeResolverInterface $themeResolver,
        private readonly NodeUrlMatcher $innerMatcher
    ) {
    }

    /**
     * {@inheritdoc}
     */
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

    public function setContext(RequestContext $context): void
    {
        $this->innerMatcher->setContext($context);
    }

    public function getContext(): RequestContext
    {
        return $this->innerMatcher->getContext();
    }

    public function matchRequest(Request $request): array
    {
        return $this->match($request->getPathInfo());
    }

    public function getSupportedFormatExtensions(): array
    {
        return $this->innerMatcher->getSupportedFormatExtensions();
    }

    public function getDefaultSupportedFormatExtension(): string
    {
        return $this->innerMatcher->getDefaultSupportedFormatExtension();
    }

    public function matchNode(string $decodedUrl, ?Theme $theme): array
    {
        return $this->innerMatcher->matchNode(
            $decodedUrl,
            $theme
        );
    }
}
