<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityHandler;

use Doctrine\Common\Collections\Criteria;
use RZ\Roadiz\Core\Handlers\AbstractHandler;
use RZ\Roadiz\CoreBundle\Entity\Tag;

/**
 * Handle operations with tags entities.
 */
final class TagHandler extends AbstractHandler
{
    private ?Tag $tag = null;

    public function getTag(): Tag
    {
        return $this->tag ?? throw new \RuntimeException('Tag is not initialized.');
    }

    /**
     * @return $this
     */
    public function setTag(Tag $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Remove only current tag children.
     *
     * @return $this
     */
    private function removeChildren(): static
    {
        /** @var Tag $tag */
        foreach ($this->getTag()->getChildren() as $tag) {
            $handler = new TagHandler($this->objectManager);
            $handler->setTag($tag);
            $handler->removeWithChildrenAndAssociations();
        }

        return $this;
    }

    /**
     * Remove only current tag associations.
     *
     * @return $this
     */
    public function removeAssociations(): static
    {
        foreach ($this->getTag()->getTranslatedTags() as $tt) {
            $this->objectManager->remove($tt);
        }

        return $this;
    }

    /**
     * Remove current tag with its children recursively and
     * its associations.
     *
     * @return $this
     */
    public function removeWithChildrenAndAssociations(): static
    {
        $this->removeChildren();
        $this->removeAssociations();

        $this->objectManager->remove($this->getTag());

        /*
         * Final flush
         */
        $this->objectManager->flush();

        return $this;
    }

    /**
     * Clean position for current tag siblings.
     *
     * @return float Return the next position after the **last** tag
     */
    #[\Override]
    public function cleanPositions(bool $setPositions = true): float
    {
        if (null !== $parent = $this->getTag()->getParent()) {
            $tagHandler = new TagHandler($this->objectManager);
            $tagHandler->setTag($parent);

            return $tagHandler->cleanChildrenPositions($setPositions);
        }

        return $this->cleanRootTagsPositions($setPositions);
    }

    /**
     * Reset current tag children positions.
     *
     * Warning, this method does not flush.
     *
     * @return float Return the next position after the **last** tag
     */
    public function cleanChildrenPositions(bool $setPositions = true): float
    {
        /*
         * Force collection to sort on position
         */
        $sort = Criteria::create();
        $sort->orderBy([
            'position' => Criteria::ASC,
        ]);

        $children = $this->getTag()->getChildren()->matching($sort);
        $i = 1;
        /** @var Tag $child */
        foreach ($children as $child) {
            if ($setPositions) {
                $child->setPosition($i);
            }
            ++$i;
        }

        return $i;
    }

    /**
     * Reset every root tags positions.
     *
     * Warning, this method does not flush.
     *
     * @return float Return the next position after the **last** tag
     */
    public function cleanRootTagsPositions(bool $setPositions = true): float
    {
        $tags = $this->objectManager
            ->getRepository(Tag::class)
            ->findBy(['parent' => null], ['position' => 'ASC']);

        $i = 1;
        /** @var Tag $child */
        foreach ($tags as $child) {
            if ($setPositions) {
                $child->setPosition($i);
            }
            ++$i;
        }

        return $i;
    }
}
