<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Node;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Redirection;
use RZ\Roadiz\CoreBundle\EntityHandler\NodeHandler;
use RZ\Roadiz\CoreBundle\Event\Redirection\PostUpdatedRedirectionEvent;
use RZ\Roadiz\CoreBundle\Node\Exception\SameNodeUrlException;
use RZ\Roadiz\CoreBundle\Repository\EntityRepository;
use RZ\Roadiz\CoreBundle\Routing\NodeRouter;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Cache\ResettableInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class NodeMover
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private UrlGeneratorInterface $urlGenerator,
        private HandlerFactoryInterface $handlerFactory,
        private EventDispatcherInterface $dispatcher,
        private CacheItemPoolInterface $cacheAdapter,
        private LoggerInterface $logger,
    ) {
    }

    private function getManager(): ObjectManager
    {
        $manager = $this->managerRegistry->getManagerForClass(Redirection::class);
        if (null === $manager) {
            throw new \RuntimeException('No manager was found during transtyping.');
        }

        return $manager;
    }

    /**
     * Warning: this method DOES NOT flush entity manager.
     */
    public function move(
        Node $node,
        ?Node $parentNode,
        float $position,
        bool $force = false,
        bool $cleanPosition = true,
    ): Node {
        if ($node->isLocked() && false === $force) {
            throw new BadRequestHttpException('Locked node cannot be moved.');
        }

        if ($node->getParent() !== $parentNode) {
            $node->setParent($parentNode);
        }

        $node->setPosition($position);

        if ($cleanPosition) {
            $this->getManager()->flush();
            /** @var NodeHandler $nodeHandler */
            $nodeHandler = $this->handlerFactory->getHandler($node);
            $nodeHandler->setNode($node);
            $nodeHandler->cleanPositions();
        }

        if ($this->cacheAdapter instanceof ResettableInterface) {
            $this->cacheAdapter->reset();
        }

        return $node;
    }

    public function getNodeSourcesUrls(Node $node): array
    {
        $paths = [];
        $lastUrl = null;
        /** @var NodesSources $nodeSource */
        foreach ($node->getNodeSources() as $nodeSource) {
            $url = $this->urlGenerator->generate(
                RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                [
                    RouteObjectInterface::ROUTE_OBJECT => $nodeSource,
                ]
            );
            if (null !== $lastUrl && $url === $lastUrl) {
                throw new SameNodeUrlException('NodeSource URL are the same between translations.');
            }
            $paths[$nodeSource->getTranslation()->getLocale()] = $url;
            $this->logger->debug(
                'Redirect '.$nodeSource->getId().' '.$nodeSource->getTranslation()->getLocale().': '.$url
            );
            $lastUrl = $url;
        }

        return $paths;
    }

    public function redirectAll(Node $node, array $previousPaths, bool $permanently = true): void
    {
        if (count($previousPaths) > 0) {
            /** @var NodesSources $nodeSource */
            foreach ($node->getNodeSources() as $nodeSource) {
                if (!empty($previousPaths[$nodeSource->getTranslation()->getLocale()])) {
                    $this->redirect(
                        $nodeSource,
                        $previousPaths[$nodeSource->getTranslation()->getLocale()],
                        $permanently
                    );
                }
            }
        }
    }

    /**
     * Warning: this method DOES NOT flush entity manager.
     */
    protected function redirect(NodesSources $nodeSource, string $previousPath, bool $permanently = true): NodesSources
    {
        if (empty($previousPath) || '/' === $previousPath) {
            $this->logger->warning('Cannot redirect empty or root path: '.$nodeSource->getTitle());

            return $nodeSource;
        }

        $newPath = $this->urlGenerator->generate(
            RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
            [
                RouteObjectInterface::ROUTE_OBJECT => $nodeSource,
                NodeRouter::NO_CACHE_PARAMETER => true, // do not use nodeSourceUrl cache provider
            ]
        );

        /*
         * Only creates redirection if path changed
         */
        if ($previousPath !== $newPath) {
            /** @var EntityRepository $redirectionRepo */
            $redirectionRepo = $this->managerRegistry->getRepository(Redirection::class);

            /*
             * Checks if new node path is already registered as
             * a redirection --> remove redirection.
             */
            $loopingRedirection = $redirectionRepo->findOneBy([
                'query' => $newPath,
            ]);
            if (null !== $loopingRedirection) {
                $this->getManager()->remove($loopingRedirection);
            }

            /** @var Redirection|null $existingRedirection */
            $existingRedirection = $redirectionRepo->findOneBy([
                'query' => $previousPath,
            ]);
            if (null === $existingRedirection) {
                $existingRedirection = new Redirection();
                $this->getManager()->persist($existingRedirection);
                $existingRedirection->setQuery($previousPath);
                $this->logger->info('New redirection created', [
                    'oldPath' => $previousPath,
                    'nodeSource' => $nodeSource,
                ]);
            }
            $existingRedirection->setRedirectNodeSource($nodeSource);
            if ($permanently) {
                $existingRedirection->setType(Response::HTTP_MOVED_PERMANENTLY);
            } else {
                $existingRedirection->setType(Response::HTTP_FOUND);
            }
            $this->dispatcher->dispatch(new PostUpdatedRedirectionEvent($existingRedirection));
        }

        return $nodeSource;
    }
}
