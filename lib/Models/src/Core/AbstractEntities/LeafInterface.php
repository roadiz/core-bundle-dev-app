<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\AbstractEntities;

use Doctrine\Common\Collections\Collection;

/**
 * @template TSelf of LeafInterface
 */
interface LeafInterface extends PositionedInterface
{
    /**
     * @return Collection<int, TSelf>
     */
    public function getChildren(): Collection;

    /**
     * @param TSelf $child
     *
     * @return $this
     */
    public function addChild(LeafInterface $child): static;

    /**
     * @param TSelf $child
     *
     * @return $this
     */
    public function removeChild(LeafInterface $child): static;

    /**
     * Do not add static return type because of Doctrine Proxy.
     *
     * @return TSelf|null
     */
    public function getParent(): ?LeafInterface;

    /**
     * @return TSelf[]
     */
    public function getParents(): array;

    /**
     * @param TSelf|null $parent
     *
     * @return $this
     */
    public function setParent(?LeafInterface $parent = null): static;

    /**
     * Gets the leaf depth.
     */
    public function getDepth(): int;
}
