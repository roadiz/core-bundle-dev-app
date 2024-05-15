<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Controller\RedirectionController;
use RZ\Roadiz\CoreBundle\Entity\Redirection;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

/**
 * UrlMatcher which tries to grab Node and Translation
 * information for a route.
 */
final class RedirectionMatcher extends UrlMatcher
{
    public function __construct(
        RequestContext $context,
        private readonly RedirectionPathResolver $pathResolver,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct(new RouteCollection(), $context);
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
        if (null !== $redirection = $this->matchRedirection($decodedUrl)) {
            $this->logger->debug('Matched redirection.', ['query' => $redirection->getQuery()]);
            return [
                '_controller' => RedirectionController::class . '::redirectAction',
                'redirection' => $redirection,
                '_route' => null,
            ];
        }

        throw new ResourceNotFoundException(sprintf('%s did not match any Doctrine Redirection', $pathinfo));
    }

    /**
     * @param string $decodedUrl
     * @return Redirection|null
     */
    protected function matchRedirection(string $decodedUrl): ?Redirection
    {
        $resource = $this->pathResolver->resolvePath($decodedUrl)->getResource();
        return $resource instanceof Redirection ? $resource : null;
    }
}
