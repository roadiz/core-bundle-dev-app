<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\TwigExtension;

use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Error\RuntimeError;
use Twig\Extension\AbstractExtension;
use Twig\Node\Node;
use Twig\TwigFunction;

/**
 * Override Symfony RoutingExtension to support object url generation.
 */
class RoutingExtension extends AbstractExtension
{
    private UrlGeneratorInterface $generator;
    private \Symfony\Bridge\Twig\Extension\RoutingExtension $decorated;

    /**
     * @param UrlGeneratorInterface $generator
     * @param \Symfony\Bridge\Twig\Extension\RoutingExtension $decorated
     */
    public function __construct(
        \Symfony\Bridge\Twig\Extension\RoutingExtension $decorated,
        UrlGeneratorInterface $generator
    ) {
        $this->generator = $generator;
        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('url', [$this, 'getUrl'], ['is_safe_callback' => [$this, 'isUrlGenerationSafe']]),
            new TwigFunction('path', [$this, 'getPath'], ['is_safe_callback' => [$this, 'isUrlGenerationSafe']]),
        ];
    }

    /**
     * @param string|object|null $name
     * @param array $parameters
     * @param bool $relative
     * @return string
     * @throws RuntimeError
     */
    public function getPath($name, array $parameters = [], bool $relative = false): string
    {
        if (is_string($name)) {
            return $this->decorated->getPath(
                $name,
                $parameters,
                $relative
            );
        }
        if (null !== $name) {
            return $this->generator->generate(
                RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                array_merge($parameters, [RouteObjectInterface::ROUTE_OBJECT => $name]),
                $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH
            );
        }
        throw new RuntimeError('Cannot generate url with NULL route name');
    }

    /**
     * @param string|object|null $name
     * @param array $parameters
     * @param bool $schemeRelative
     * @return string
     * @throws RuntimeError
     */
    public function getUrl($name, array $parameters = [], bool $schemeRelative = false): string
    {
        if (is_string($name)) {
            return $this->decorated->getUrl(
                $name,
                $parameters,
                $schemeRelative
            );
        }
        if (null !== $name) {
            return $this->generator->generate(
                RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                array_merge($parameters, [RouteObjectInterface::ROUTE_OBJECT => $name]),
                $schemeRelative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL
            );
        }
        throw new RuntimeError('Cannot generate url with NULL route name');
    }

    public function isUrlGenerationSafe(Node $argsNode): array
    {
        return $this->decorated->isUrlGenerationSafe($argsNode);
    }
}
