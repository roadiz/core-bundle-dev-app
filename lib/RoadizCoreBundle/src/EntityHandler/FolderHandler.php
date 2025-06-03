<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityHandler;

use Doctrine\Common\Collections\Criteria;
use RZ\Roadiz\Core\Handlers\AbstractHandler;
use RZ\Roadiz\CoreBundle\Entity\Folder;

/**
 * Handle operations with folders entities.
 */
final class FolderHandler extends AbstractHandler
{
    protected ?Folder $folder = null;

    public function getFolder(): Folder
    {
        if (null === $this->folder) {
            throw new \BadMethodCallException('Folder is null');
        }

        return $this->folder;
    }

    /**
     * @return $this
     */
    public function setFolder(Folder $folder): self
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * Remove only current folder children.
     *
     * @return $this
     */
    private function removeChildren(): self
    {
        /** @var Folder $folder */
        foreach ($this->getFolder()->getChildren() as $folder) {
            $handler = new FolderHandler($this->objectManager);
            $handler->setFolder($folder);
            $handler->removeWithChildrenAndAssociations();
        }

        return $this;
    }

    /**
     * Remove current folder with its children recursively and
     * its associations.
     *
     * @return $this
     */
    public function removeWithChildrenAndAssociations(): self
    {
        $this->removeChildren();
        $this->objectManager->remove($this->getFolder());

        /*
         * Final flush
         */
        $this->objectManager->flush();

        return $this;
    }

    /**
     * Clean position for current folder siblings.
     *
     * @return float Return the next position after the **last** folder
     */
    #[\Override]
    public function cleanPositions(bool $setPositions = true): float
    {
        if (null !== $this->getFolder()->getParent()) {
            $parentHandler = new FolderHandler($this->objectManager);
            /** @var Folder|null $parent */
            $parent = $this->getFolder()->getParent();
            $parentHandler->setFolder($parent);

            return $parentHandler->cleanChildrenPositions($setPositions);
        } else {
            return $this->cleanRootFoldersPositions($setPositions);
        }
    }

    /**
     * Reset current folder children positions.
     *
     * Warning, this method does not flush.
     *
     * @return float Return the next position after the **last** folder
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

        $children = $this->getFolder()->getChildren()->matching($sort);
        $i = 1;
        /** @var Folder $child */
        foreach ($children as $child) {
            if ($setPositions) {
                $child->setPosition($i);
            }
            ++$i;
        }

        return $i;
    }

    /**
     * Reset every root folders positions.
     *
     * Warning, this method does not flush.
     *
     * @return float Return the next position after the **last** folder
     */
    public function cleanRootFoldersPositions(bool $setPositions = true): float
    {
        /** @var Folder[] $folders */
        $folders = $this->objectManager
            ->getRepository(Folder::class)
            ->findBy(['parent' => null], ['position' => 'ASC']);

        $i = 1;
        foreach ($folders as $child) {
            if ($setPositions) {
                $child->setPosition($i);
            }
            ++$i;
        }

        return $i;
    }
}
