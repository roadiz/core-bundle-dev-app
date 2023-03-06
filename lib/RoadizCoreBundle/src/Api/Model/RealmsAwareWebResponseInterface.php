<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Model;

use RZ\Roadiz\CoreBundle\Model\RealmInterface;

interface RealmsAwareWebResponseInterface extends WebResponseInterface
{
    /**
     * @return RealmInterface[]|null
     */
    public function getRealms(): ?array;

    /**
     * @param RealmInterface[]|null $realms
     * @return RealmsAwareWebResponseInterface
     */
    public function setRealms(?array $realms): RealmsAwareWebResponseInterface;

    /**
     * @return bool
     */
    public function isHidingBlocks(): bool;

    /**
     * @param bool $hidingBlocks
     * @return RealmsAwareWebResponseInterface
     */
    public function setHidingBlocks(bool $hidingBlocks): RealmsAwareWebResponseInterface;
}
