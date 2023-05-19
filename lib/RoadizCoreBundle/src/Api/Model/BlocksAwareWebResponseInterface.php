<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Model;

use Doctrine\Common\Collections\Collection;
use RZ\TreeWalker\WalkerInterface;

interface BlocksAwareWebResponseInterface extends WebResponseInterface
{
    /**
     * @return Collection<int, WalkerInterface>|null
     */
    public function getBlocks(): ?Collection;

    /**
     * @param Collection<int, WalkerInterface>|null $blocks
     * @return BlocksAwareWebResponseInterface
     */
    public function setBlocks(?Collection $blocks): BlocksAwareWebResponseInterface;
}
