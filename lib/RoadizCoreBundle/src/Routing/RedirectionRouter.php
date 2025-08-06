<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Cmf\Component\Routing\VersatileGeneratorInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router;
use Symfony\Component\Stopwatch\Stopwatch;

class RedirectionRouter extends Router implements VersatileGeneratorInterface
{
    public function __construct(
        RedirectionMatcher $matcher,
        protected readonly ManagerRegistry $managerRegistry,
        protected readonly Stopwatch $stopwatch,
        array $options = [],
        ?RequestContext $context = null,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct(
            new NullLoader(),
            null,
            $options,
            $context,
            $logger
        );
        $this->matcher = $matcher;
    }

    #[\Override]
    public function getRouteCollection(): RouteCollection
    {
        return new RouteCollection();
    }

    #[\Override]
    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        throw new RouteNotFoundException(static::class.' does not support path generation.');
    }

    /**
     * No generator for a node router.
     */
    #[\Override]
    public function getGenerator(): UrlGeneratorInterface
    {
        throw new \BadMethodCallException(static::class.' does not support path generation.');
    }

    #[\Override]
    public function getRouteDebugMessage(mixed $name, array $parameters = []): string
    {
        return 'RedirectionRouter does not support path generation.';
    }
}
