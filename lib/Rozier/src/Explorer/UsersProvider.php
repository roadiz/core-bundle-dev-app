<?php

declare(strict_types=1);

namespace Themes\Rozier\Explorer;

use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Explorer\AbstractDoctrineExplorerProvider;

final class UsersProvider extends AbstractDoctrineExplorerProvider
{
    #[\Override]
    protected function getProvidedClassname(): string
    {
        return User::class;
    }

    #[\Override]
    protected function getDefaultCriteria(): array
    {
        return [];
    }

    #[\Override]
    protected function getDefaultOrdering(): array
    {
        return ['username' => 'ASC'];
    }

    #[\Override]
    public function supports(mixed $item): bool
    {
        if ($item instanceof User) {
            return true;
        }

        return false;
    }
}
