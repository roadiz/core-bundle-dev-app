<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\AbstractEntities;

use Doctrine\Common\Collections\Collection;

trait LeafTrait
{
    use PositionedTrait;

    /**
     * @return Collection<int, self>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
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
        /* @phpstan-ignore-next-line */
        return $this->parent;
    }

    /**
     * @return LeafInterface[]
     */
    public function getParents(): array
    {
        $parentsArray = [];
        $parent = $this;

        do {
            $parent = $parent->getParent();
            if (null !== $parent) {
                $parentsArray[] = $parent;
            }
        } while (null !== $parent);

        return array_reverse($parentsArray);
    }

    /**
     * Gets the nodes' depth.
     */
    public function getDepth(): int
    {
        if (null === $this->getParent()) {
            return 0;
        }

        return $this->getParent()->getDepth() + 1;
    }
}
