<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Theme;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesPathGeneratingEvent;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Cmf\Component\Routing\VersatileGeneratorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router;

class NodeRouter extends Router implements VersatileGeneratorInterface
{
    /**
     * @var string
     */
    public const NO_CACHE_PARAMETER = '_no_cache';
    private ?Theme $theme = null;

    public function __construct(
        NodeUrlMatcherInterface $matcher,
        protected readonly Settings $settingsBag,
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly CacheItemPoolInterface $nodeSourceUrlCacheAdapter,
        RequestContext $context,
        LoggerInterface $logger,
        array $options = [],
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

    public function getRouteCollection(): RouteCollection
    {
        return new RouteCollection();
    }

    /**
     * Gets the UrlMatcher instance associated with this Router.
     */
    public function getMatcher(): UrlMatcherInterface
    {
        return $this->matcher;
    }

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(?Theme $theme): NodeRouter
    {
        $this->theme = $theme;

        return $this;
    }

    public function getRouteDebugMessage(string $name, array $parameters = []): string
    {
        if (RouteObjectInterface::OBJECT_BASED_ROUTE_NAME === $name) {
            if (
                array_key_exists(RouteObjectInterface::ROUTE_OBJECT, $parameters)
                && $parameters[RouteObjectInterface::ROUTE_OBJECT] instanceof NodesSources
            ) {
                $route = $parameters[RouteObjectInterface::ROUTE_OBJECT];

                return '['.$route->getTranslation()->getLocale().']'.
                    $route->getTitle().' - '.
                    $route->getNode()->getNodeName().
                    '['.$route->getNode()->getId().']';
            }
        }

        return (string) $name;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        if (RouteObjectInterface::OBJECT_BASED_ROUTE_NAME !== $name) {
            throw new RouteNotFoundException();
        }

        if (
            array_key_exists(RouteObjectInterface::ROUTE_OBJECT, $parameters)
            && $parameters[RouteObjectInterface::ROUTE_OBJECT] instanceof NodesSources
        ) {
            $route = $parameters[RouteObjectInterface::ROUTE_OBJECT];
            unset($parameters[RouteObjectInterface::ROUTE_OBJECT]);
        } else {
            $route = null;
        }

        if (!$route instanceof NodesSources) {
            throw new RouteNotFoundException();
        }

        if (!empty($parameters['canonicalScheme'])) {
            $schemeAuthority = trim($parameters['canonicalScheme']);
            unset($parameters['canonicalScheme']);
        } else {
            $schemeAuthority = $this->getContext()->getScheme().'://'.$this->getHttpHost();
        }

        $noCache = false;
        if (!empty($parameters[static::NO_CACHE_PARAMETER])) {
            $noCache = (bool) $parameters[static::NO_CACHE_PARAMETER];
        }

        $nodePathInfo = $this->getResourcePath($route, $parameters, $noCache);

        /*
         * If node path is complete, do not alter path anymore.
         */
        if (true === $nodePathInfo->isComplete()) {
            if (self::ABSOLUTE_URL == $referenceType && !$nodePathInfo->containsScheme()) {
                return $schemeAuthority.$nodePathInfo->getPath();
            }

            return $nodePathInfo->getPath();
        }

        $queryString = '';
        $parameters = $nodePathInfo->getParameters();
        $matcher = $this->getMatcher();

        if (
            isset($parameters['_format'])
            && $matcher instanceof NodeUrlMatcher
            && in_array($parameters['_format'], $matcher->getSupportedFormatExtensions())
        ) {
            unset($parameters['_format']);
        }
        if (array_key_exists(static::NO_CACHE_PARAMETER, $parameters)) {
            unset($parameters[static::NO_CACHE_PARAMETER]);
        }
        if (count($parameters) > 0) {
            $queryString = '?'.http_build_query($parameters);
        }

        if (self::ABSOLUTE_URL == $referenceType) {
            // Absolute path
            return $schemeAuthority.$this->getContext()->getBaseUrl().'/'.$nodePathInfo->getPath().$queryString;
        }

        // ABSOLUTE_PATH
        return $this->getContext()->getBaseUrl().'/'.$nodePathInfo->getPath().$queryString;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function getResourcePath(
        NodesSources $source,
        array $parameters = [],
        bool $noCache = false,
    ): NodePathInfo {
        if ($noCache) {
            $parametersHash = sha1(serialize($parameters));
            $cacheKey = 'ns_url_'.$source->getId().'_'.$this->getContext()->getHost().'_'.$parametersHash;
            $cacheItem = $this->nodeSourceUrlCacheAdapter->getItem($cacheKey);
            if (!$cacheItem->isHit()) {
                $cacheItem->set($this->getNodesSourcesPath($source, $parameters));
                $this->nodeSourceUrlCacheAdapter->save($cacheItem);
            }

            return $cacheItem->get();
        }

        return $this->getNodesSourcesPath($source, $parameters);
    }

    protected function getNodesSourcesPath(NodesSources $source, array $parameters = []): NodePathInfo
    {
        $event = new NodesSourcesPathGeneratingEvent(
            $this->getTheme(),
            $source,
            $this->getContext(),
            $parameters,
            (bool) $this->settingsBag->get('force_locale'),
            (bool) $this->settingsBag->get('force_locale_with_urlaliases')
        );
        /*
         * Dispatch node-source URL generation to any listener
         */
        $this->eventDispatcher->dispatch($event);
        /*
         * Get path, parameters and isComplete back from event propagation.
         */
        $nodePathInfo = new NodePathInfo();
        $nodePathInfo->setPath($event->getPath());
        $nodePathInfo->setParameters($event->getParameters());
        $nodePathInfo->setComplete($event->isComplete());
        $nodePathInfo->setContainsScheme($event->containsScheme());

        if (null === $nodePathInfo->getPath()) {
            throw new InvalidParameterException('NodeSource generated path is null.');
        }

        return $nodePathInfo;
    }

    /**
     * Returns the HTTP host being requested.
     *
     * The port name will be appended to the host if it's non-standard.
     */
    private function getHttpHost(): string
    {
        $scheme = $this->getContext()->getScheme();

        $port = '';
        if ('http' === $scheme && 80 != $this->context->getHttpPort()) {
            $port = ':'.$this->context->getHttpPort();
        } elseif ('https' === $scheme && 443 != $this->context->getHttpsPort()) {
            $port = ':'.$this->context->getHttpsPort();
        }

        return $this->getContext()->getHost().$port;
    }
}
