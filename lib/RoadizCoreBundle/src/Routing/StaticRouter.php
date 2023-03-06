<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router;

/**
 * Router class which takes a DeferredRouteCollection instead of YamlLoader.
 */
class StaticRouter extends Router
{
    protected DeferredRouteCollection $routeCollection;

    /**
     * @param DeferredRouteCollection $routeCollection
     * @param array $options
     * @param RequestContext|null $context
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        DeferredRouteCollection $routeCollection,
        array $options = [],
        RequestContext $context = null,
        LoggerInterface $logger = null
    ) {
        parent::__construct(
            new NullLoader(),
            null,
            $options,
            $context,
            $logger
        );
        $this->routeCollection = $routeCollection;
    }

    /**
     * @return RouteCollection
     */
    public function getRouteCollection(): RouteCollection
    {
        if (null === $this->collection) {
            $this->routeCollection->parseResources();
            $this->collection = $this->routeCollection;
        }
        return $this->collection;
    }
}
