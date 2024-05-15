<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityHandler;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\Core\Handlers\AbstractHandler;

/**
 * Handle operations with tags entities.
 */
final class TagHandler extends AbstractHandler
{
    private ?Tag $tag = null;

    /**
     * @param Tag $tag
     * @return $this
     */
    public function setTag(Tag $tag): self
    {
        $this->tag = $tag;
        return $this;
    }

    /**
     * Remove only current tag children.
     *
     * @return $this
     */
    private function removeChildren(): self
    {
        /** @var Tag $tag */
        foreach ($this->tag->getChildren() as $tag) {
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
    public function removeAssociations(): self
    {
        foreach ($this->tag->getTranslatedTags() as $tt) {
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
    public function removeWithChildrenAndAssociations(): self
    {
        $this->removeChildren();
        $this->removeAssociations();

        $this->objectManager->remove($this->tag);

        /*
         * Final flush
         */
        $this->objectManager->flush();

        return $this;
    }

    /**
     * Clean position for current tag siblings.
     *
     * @param bool $setPositions
     * @return float Return the next position after the **last** tag
     */
    public function cleanPositions(bool $setPositions = true): float
    {
        if ($this->tag->getParent() !== null) {
            $tagHandler = new TagHandler($this->objectManager);
            /** @var Tag|null $parent */
            $parent = $this->tag->getParent();
            $tagHandler->setTag($parent);
            return $tagHandler->cleanChildrenPositions($setPositions);
        } else {
            return $this->cleanRootTagsPositions($setPositions);
        }
    }

    /**
     * Reset current tag children positions.
     *
     * Warning, this method does not flush.
     *
     * @param bool $setPositions
     * @return float Return the next position after the **last** tag
     */
    public function cleanChildrenPositions(bool $setPositions = true): float
    {
        /*
         * Force collection to sort on position
         */
        $sort = Criteria::create();
        $sort->orderBy([
            'position' => Criteria::ASC
        ]);

        $children = $this->tag->getChildren()->matching($sort);
        $i = 1;
        /** @var Tag $child */
        foreach ($children as $child) {
            if ($setPositions) {
                $child->setPosition($i);
            }
            $i++;
        }

        return $i;
    }

    /**
     * Reset every root tags positions.
     *
     * Warning, this method does not flush.
     *
     * @param bool $setPositions
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
            $i++;
        }

        return $i;
    }
}
