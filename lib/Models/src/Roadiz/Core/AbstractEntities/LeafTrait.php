<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\AbstractEntities;

use Doctrine\Common\Collections\Collection;

trait LeafTrait
{
    use PositionedTrait;

    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * @param Collection<int, static> $children
     * @return $this
     */
    public function setChildren(Collection $children): static
    {
        $this->children = $children;
        /** @var static $child */
        foreach ($this->children as $child) {
            $child->setParent($this);
        }
        return $this;
    }

    /**
     * @param static $child
     * @return $this
     */
    public function addChild(LeafInterface $child): static
    {
        if (!$this->getChildren()->contains($child)) {
            $this->getChildren()->add($child);
            $child->setParent($this);
        }

        return $this;
    }
    /**
     * @param static $child
     * @return $this
     */
    public function removeChild(LeafInterface $child): static
    {
        if ($this->getChildren()->contains($child)) {
            $this->getChildren()->removeElement($child);
            $child->setParent(null);
        }

        return $this;
    }

    /*
     * Do not add static return type because of Doctrine Proxy.
     */
    /**
     * @return static|null
     */
    public function getParent(): ?LeafInterface
    {
        /** @phpstan-ignore-next-line */
        return $this->parent;
    }

    /**
     * @param static|null $parent
     * @return $this
     */
    public function setParent(?LeafInterface $parent = null): static
    {
        if ($parent === $this) {
            throw new \InvalidArgumentException('An entity cannot have itself as a parent.');
        }

        $this->parent = $parent;
        $this->parent?->addChild($this);

        return $this;
    }

    /**
     * @return static[]
     */
    public function getParents(): array
    {
        $parentsArray = [];
        $parent = $this;

        do {
            $parent = $parent->getParent();
            if ($parent !== null) {
                $parentsArray[] = $parent;
            }
        } while ($parent !== null);

        return array_reverse($parentsArray);
    }

    /**
     * Gets the nodes' depth.
     *
     * @return int
     */
    public function getDepth(): int
    {
        if ($this->getParent() === null) {
            return 0;
        }
        return $this->getParent()->getDepth() + 1;
    }
}
