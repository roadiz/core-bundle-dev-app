<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Explorer\Provider;

use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Explorer\AbstractDoctrineExplorerProvider;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('roadiz.explorer_provider')]
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
