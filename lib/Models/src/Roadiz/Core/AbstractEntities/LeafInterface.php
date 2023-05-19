<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\AbstractEntities;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Comparable;

interface LeafInterface extends PositionedInterface, Comparable
{
    public function getChildren(): Collection;

    /**
     * @param static $child
     * @return $this
     */
    public function addChild(LeafInterface $child): static;

    /**
     * @param static $child
     * @return $this
     */
    public function removeChild(LeafInterface $child): static;

    /**
     * Do not add static return type because of Doctrine Proxy.
     *
     * @return static|null
     */
    public function getParent(): ?LeafInterface;

    /**
     * @return static[]
     */
    public function getParents(): array;

    /**
     * @param static|null $parent
     * @return $this
     */
    public function setParent(?LeafInterface $parent = null): static;

    /**
     * Gets the leaf depth.
     *
     * @return int
     */
    public function getDepth(): int;
}
