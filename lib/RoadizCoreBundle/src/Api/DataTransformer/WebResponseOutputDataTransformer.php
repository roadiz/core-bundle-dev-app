<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\DataTransformer;

use Psr\Cache\CacheItemPoolInterface;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Api\Breadcrumbs\BreadcrumbsFactoryInterface;
use RZ\Roadiz\CoreBundle\Api\Model\NodesSourcesHeadFactoryInterface;
use RZ\Roadiz\CoreBundle\Api\Model\WebResponse;
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

    private NodesSourcesHeadFactoryInterface $nodesSourcesHeadFactory;
    private BreadcrumbsFactoryInterface $breadcrumbsFactory;
    private WalkerContextInterface $walkerContext;
    private CacheItemPoolInterface $cacheItemPool;
    private UrlGeneratorInterface $urlGenerator;
    private RealmResolverInterface $realmResolver;
    private TreeWalkerGenerator $treeWalkerGenerator;

    public function __construct(
        NodesSourcesHeadFactoryInterface $nodesSourcesHeadFactory,
        BreadcrumbsFactoryInterface $breadcrumbsFactory,
        WalkerContextInterface $walkerContext,
        CacheItemPoolInterface $cacheItemPool,
        UrlGeneratorInterface $urlGenerator,
        RealmResolverInterface $realmResolver,
        TreeWalkerGenerator $treeWalkerGenerator
    ) {
        $this->nodesSourcesHeadFactory = $nodesSourcesHeadFactory;
        $this->breadcrumbsFactory = $breadcrumbsFactory;
        $this->walkerContext = $walkerContext;
        $this->cacheItemPool = $cacheItemPool;
        $this->urlGenerator = $urlGenerator;
        $this->realmResolver = $realmResolver;
        $this->treeWalkerGenerator = $treeWalkerGenerator;
    }

    protected function getWalkerContext(): WalkerContextInterface
    {
        return $this->walkerContext;
    }

    protected function getCacheItemPool(): CacheItemPoolInterface
    {
        return $this->cacheItemPool;
    }

    protected function getChildrenNodeSourceWalkerMaxLevel(): int
    {
        return 5;
    }

    public function getTreeWalkerGenerator(): TreeWalkerGenerator
    {
        return $this->treeWalkerGenerator;
    }

    /**
     * @return class-string<AbstractWalker>
     */
    protected function getChildrenNodeSourceWalkerClassname(): string
    {
        return AutoChildrenNodeSourceWalker::class;
    }

    protected function getRealmResolver(): RealmResolverInterface
    {
        return $this->realmResolver;
    }

    /**
     * @inheritDoc
     */
    public function transform($object, string $to, array $context = []): ?WebResponseInterface
    {
        if (!$object instanceof PersistableInterface) {
            throw new \InvalidArgumentException(
                'Data to transform must be instance of ' .
                PersistableInterface::class
            );
        }
        $output = new WebResponse();
        $output->item = $object;
        if ($object instanceof NodesSources) {
            $this->injectRealms($output, $object);
            $this->injectBlocks($output, $object);

            $output->path = $this->urlGenerator->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, [
                RouteObjectInterface::ROUTE_OBJECT => $object
            ], UrlGeneratorInterface::ABSOLUTE_PATH);
            $output->head = $this->nodesSourcesHeadFactory->createForNodeSource($object);
            $output->breadcrumbs = $this->breadcrumbsFactory->create($object);
        }
        if ($object instanceof TranslationInterface) {
            $output->head = $this->nodesSourcesHeadFactory->createForTranslation($object);
        }
        return $output;
    }

    /**
     * @inheritDoc
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return WebResponseInterface::class === $to && $data instanceof PersistableInterface;
    }
}
