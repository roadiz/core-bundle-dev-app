<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Model;

use Doctrine\Common\Collections\Collection;
use RZ\TreeWalker\WalkerInterface;

interface BlocksAwareWebResponseInterface extends WebResponseInterface
{
    /**
     * @return WalkerInterface[]|null
     */
    public function getBlocks(): ?array;

    /**
     * @param Collection<int, WalkerInterface>|null $blocks
     *
     * @return $this
     */
    public function setBlocks(?Collection $blocks): static;
}
