<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\DataTransformer;

use Psr\Cache\CacheItemPoolInterface;
use RZ\Roadiz\CoreBundle\Api\Model\BlocksAwareWebResponseInterface;
use RZ\Roadiz\CoreBundle\Api\Model\RealmsAwareWebResponseInterface;
use RZ\Roadiz\CoreBundle\Api\Model\WebResponseInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\TreeWalker\AbstractWalker;
use RZ\TreeWalker\WalkerContextInterface;

trait BlocksAwareWebResponseOutputDataTransformerTrait
{
    abstract protected function getWalkerContext(): WalkerContextInterface;
    abstract protected function getCacheItemPool(): CacheItemPoolInterface;
    abstract protected function getChildrenNodeSourceWalkerMaxLevel(): int;

    /**
     * @return class-string<AbstractWalker>
     */
    abstract protected function getChildrenNodeSourceWalkerClassname(): string;

    /**
     * @param BlocksAwareWebResponseInterface $output
     * @param NodesSources $data
     * @return WebResponseInterface
     */
    protected function injectBlocks(BlocksAwareWebResponseInterface $output, NodesSources $data): WebResponseInterface
    {
        if (!$output instanceof RealmsAwareWebResponseInterface || !$output->isHidingBlocks()) {
            /** @var class-string<AbstractWalker> $childrenNodeSourceWalkerClassname */
            $childrenNodeSourceWalkerClassname = $this->getChildrenNodeSourceWalkerClassname();
            $output->setBlocks($childrenNodeSourceWalkerClassname::build(
                $data,
                $this->getWalkerContext(),
                $this->getChildrenNodeSourceWalkerMaxLevel(),
                $this->getCacheItemPool()
            )->getChildren());
        }

        return $output;
    }
}
