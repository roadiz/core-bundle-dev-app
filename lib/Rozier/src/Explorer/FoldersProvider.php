<?php

declare(strict_types=1);

namespace Themes\Rozier\Explorer;

use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Explorer\AbstractDoctrineExplorerProvider;

final class FoldersProvider extends AbstractDoctrineExplorerProvider
{
    #[\Override]
    protected function getProvidedClassname(): string
    {
        return Folder::class;
    }

    #[\Override]
    protected function getDefaultCriteria(): array
    {
        return [];
    }

    #[\Override]
    protected function getDefaultOrdering(): array
    {
        return ['folderName' => 'ASC'];
    }

    #[\Override]
    public function supports(mixed $item): bool
    {
        if ($item instanceof Folder) {
            return true;
        }

        return false;
    }
}
