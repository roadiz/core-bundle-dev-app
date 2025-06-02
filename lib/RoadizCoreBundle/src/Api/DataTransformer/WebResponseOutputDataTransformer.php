<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\DataTransformer;

use Psr\Cache\CacheItemPoolInterface;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Api\Breadcrumbs\BreadcrumbsFactoryInterface;
use RZ\Roadiz\CoreBundle\Api\Model\BlocksAwareWebResponseInterface;
use RZ\Roadiz\CoreBundle\Api\Model\NodesSourcesHeadFactoryInterface;
use RZ\Roadiz\CoreBundle\Api\Model\RealmsAwareWebResponseInterface;
use RZ\Roadiz\CoreBundle\Api\Model\WebResponseInterface;
use RZ\Roadiz\CoreBundle\Api\TreeWalker\AutoChildrenNodeSourceWalker;
use RZ\Roadiz\CoreBundle\Api\TreeWalker\TreeWalkerGenerator;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Realm\RealmResolverInterface;
use RZ\TreeWalker\AbstractWalker;
use RZ\TreeWalker\WalkerContextInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class WebResponseOutputDataTransformer implements WebResponseDataTransformerInterface
{
    use BlocksAwareWebResponseOutputDataTransformerTrait;
    use RealmsAwareWebResponseOutputDataTransformerTrait;

    /**
     * @param class-string<WebResponseInterface> $webResponseClass
     */
    public function __construct(
        protected readonly NodesSourcesHeadFactoryInterface $nodesSourcesHeadFactory,
        protected readonly BreadcrumbsFactoryInterface $breadcrumbsFactory,
        protected readonly WalkerContextInterface $walkerContext,
        protected readonly CacheItemPoolInterface $cacheItemPool,
        protected readonly UrlGeneratorInterface $urlGenerator,
        protected readonly RealmResolverInterface $realmResolver,
        protected readonly TreeWalkerGenerator $treeWalkerGenerator,
        private readonly string $webResponseClass,
    ) {
    }

    #[\Override]
    protected function getWalkerContext(): WalkerContextInterface
    {
        return $this->walkerContext;
    }

    #[\Override]
    protected function getCacheItemPool(): CacheItemPoolInterface
    {
        return $this->cacheItemPool;
    }

    #[\Override]
    protected function getChildrenNodeSourceWalkerMaxLevel(): int
    {
        return 5;
    }

    #[\Override]
    public function getTreeWalkerGenerator(): TreeWalkerGenerator
    {
        return $this->treeWalkerGenerator;
    }

    /**
     * @return class-string<AbstractWalker>
     */
    #[\Override]
    protected function getChildrenNodeSourceWalkerClassname(): string
    {
        return AutoChildrenNodeSourceWalker::class;
    }

    #[\Override]
    protected function getRealmResolver(): RealmResolverInterface
    {
        return $this->realmResolver;
    }

    #[\Override]
    public function createWebResponse(): WebResponseInterface
    {
        return new ($this->webResponseClass)();
    }

    #[\Override]
    public function transform(PersistableInterface $object, string $to, array $context = [], ?WebResponseInterface $output = null): ?WebResponseInterface
    {
        $output ??= $this->createWebResponse();
        $output->setItem($object);
        if ($object instanceof NodesSources) {
            if ($output instanceof RealmsAwareWebResponseInterface) {
                $this->injectRealms($output, $object);
            }
            if ($output instanceof BlocksAwareWebResponseInterface) {
                $this->injectBlocks($output, $object);
            }

            $output->setPath($this->urlGenerator->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, [
                RouteObjectInterface::ROUTE_OBJECT => $object,
            ], UrlGeneratorInterface::ABSOLUTE_PATH));
            $output->setHead($this->nodesSourcesHeadFactory->createForNodeSource($object));
            $output->setBreadcrumbs($this->breadcrumbsFactory->create($object));
            $output->setMaxAge($object->getNode()->getTtl() * 60);
        }
        if ($object instanceof TranslationInterface) {
            $output->setHead($this->nodesSourcesHeadFactory->createForTranslation($object));
        }

        return $output;
    }
}
