<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Cmf\Component\Routing\VersatileGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router;
use Symfony\Component\Stopwatch\Stopwatch;

class RedirectionRouter extends Router implements VersatileGeneratorInterface
{
    protected ManagerRegistry $managerRegistry;
    protected ?Stopwatch $stopwatch;

    /**
     * @param RedirectionMatcher $matcher
     * @param ManagerRegistry $managerRegistry
     * @param array $options
     * @param RequestContext|null $context
     * @param LoggerInterface|null $logger
     * @param Stopwatch|null $stopwatch
     */
    public function __construct(
        RedirectionMatcher $matcher,
        ManagerRegistry $managerRegistry,
        array $options = [],
        RequestContext $context = null,
        LoggerInterface $logger = null,
        Stopwatch $stopwatch = null
    ) {
        parent::__construct(
            new NullLoader(),
            null,
            $options,
            $context,
            $logger
        );
        $this->stopwatch = $stopwatch;
        $this->managerRegistry = $managerRegistry;
        $this->matcher = $matcher;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollection(): RouteCollection
    {
        return new RouteCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        return '';
    }

    /**
     * No generator for a node router.
     */
    public function getGenerator(): UrlGeneratorInterface
    {
        throw new \BadMethodCallException(get_class($this) . ' does not support path generation.');
    }

    public function supports($name): bool
    {
        return false;
    }

    public function getRouteDebugMessage($name, array $parameters = []): string
    {
        return 'RedirectionRouter does not support path generation.';
    }
}
