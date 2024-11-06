<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Theme;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * UrlMatcher which tries to grab Node and Translation information for a route.
 */
final class NodeUrlMatcher extends DynamicUrlMatcher implements NodeUrlMatcherInterface
{
    /**
     * @param class-string<AbstractController> $defaultControllerClass
     */
    public function __construct(
        private readonly PathResolverInterface $pathResolver,
        RequestContext $context,
        PreviewResolverInterface $previewResolver,
        Stopwatch $stopwatch,
        LoggerInterface $logger,
        private readonly string $defaultControllerClass,
    ) {
        parent::__construct($context, $previewResolver, $stopwatch, $logger);
    }

    public function getSupportedFormatExtensions(): array
    {
        return ['xml', 'json', 'pdf', 'html'];
    }

    public function getDefaultSupportedFormatExtension(): string
    {
        return 'html';
    }

    public function match(string $pathinfo): array
    {
        $decodedUrl = rawurldecode($pathinfo);

        /*
         * Try nodes routes
         */
        return $this->matchNode($decodedUrl, null);
    }

    protected function getNodeRouteHelper(NodesSources $nodeSource, ?Theme $theme): NodeRouteHelper
    {
        return new NodeRouteHelper(
            $nodeSource->getNode(),
            $theme,
            $this->previewResolver,
            $this->logger,
            $this->defaultControllerClass
        );
    }

    public function matchNode(string $decodedUrl, ?Theme $theme): array
    {
        $resourceInfo = $this->pathResolver->resolvePath(
            $decodedUrl,
            $this->getSupportedFormatExtensions()
        );
        $nodeSource = $resourceInfo->getResource();

        if ($nodeSource instanceof NodesSources && !$nodeSource->getNode()->isHome()) {
            $translation = $nodeSource->getTranslation();
            $nodeRouteHelper = $this->getNodeRouteHelper($nodeSource, $theme);

            if (!$this->previewResolver->isPreview() && !$translation->isAvailable()) {
                throw new ResourceNotFoundException();
            }

            if (false === $nodeRouteHelper->isViewable()) {
                throw new ResourceNotFoundException();
            }

            return [
                '_controller' => $nodeRouteHelper->getController().'::'.$nodeRouteHelper->getMethod(),
                '_locale' => $resourceInfo->getLocale(),
                '_route' => RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                '_format' => $resourceInfo->getFormat(),
                'node' => $nodeSource->getNode(),
                'nodeSource' => $nodeSource,
                RouteObjectInterface::ROUTE_OBJECT => $resourceInfo->getResource(),
                'translation' => $resourceInfo->getTranslation(),
                'theme' => $theme,
            ];
        }
        throw new ResourceNotFoundException();
    }
}
