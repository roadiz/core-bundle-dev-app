<?php

declare(strict_types=1);

namespace Themes\Rozier\Explorer;

use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Explorer\AbstractDoctrineExplorerProvider;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerItemInterface;

final class FoldersProvider extends AbstractDoctrineExplorerProvider
{
    protected function getProvidedClassname(): string
    {
        return Folder::class;
    }

    protected function getDefaultCriteria(): array
    {
        return [];
    }

    protected function getDefaultOrdering(): array
    {
        return ['folderName' => 'ASC'];
    }

    /**
     * @inheritDoc
     */
    public function supports($item): bool
    {
        if ($item instanceof Folder) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function toExplorerItem(mixed $item): ?ExplorerItemInterface
    {
        if ($item instanceof Folder) {
            return new FolderExplorerItem($item, $this->urlGenerator);
        }
        throw new \InvalidArgumentException('Explorer item must be instance of ' . Folder::class);
    }
}
