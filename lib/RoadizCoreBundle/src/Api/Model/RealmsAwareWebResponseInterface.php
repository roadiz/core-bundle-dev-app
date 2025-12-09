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
     *
     * @return $this
     */
    public function setRealms(?array $realms): static;

    public function isHidingBlocks(): bool;

    /**
     * @return $this
     */
    public function setHidingBlocks(bool $hidingBlocks): static;
}
