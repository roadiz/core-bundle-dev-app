<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * UrlMatcher which tries to grab Node and Translation
 * information for a route.
 */
abstract class DynamicUrlMatcher extends UrlMatcher
{
    public function __construct(
        RequestContext $context,
        protected readonly PreviewResolverInterface $previewResolver,
        protected readonly Stopwatch $stopwatch,
        protected readonly LoggerInterface $logger
    ) {
        parent::__construct(new RouteCollection(), $context);
    }
}
