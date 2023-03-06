<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
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
    protected Stopwatch $stopwatch;
    protected LoggerInterface $logger;
    protected PreviewResolverInterface $previewResolver;

    /**
     * @param RequestContext $context
     * @param PreviewResolverInterface $previewResolver
     * @param Stopwatch $stopwatch
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        RequestContext $context,
        PreviewResolverInterface $previewResolver,
        Stopwatch $stopwatch,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct(new RouteCollection(), $context);
        $this->stopwatch = $stopwatch;
        $this->logger = $logger ?? new NullLogger();
        $this->previewResolver = $previewResolver;
    }
}
