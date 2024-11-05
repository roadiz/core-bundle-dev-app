<?php

declare(strict_types=1);

namespace Themes\Rozier\Explorer;

use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Explorer\AbstractDoctrineExplorerProvider;

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

    public function supports(mixed $item): bool
    {
        if ($item instanceof Folder) {
            return true;
        }

        return false;
    }
}
