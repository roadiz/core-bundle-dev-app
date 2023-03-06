<?php

declare(strict_types=1);

namespace Themes\Rozier\Explorer;

use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Explorer\AbstractDoctrineExplorerProvider;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerItemInterface;

final class UsersProvider extends AbstractDoctrineExplorerProvider
{
    protected function getProvidedClassname(): string
    {
        return User::class;
    }

    protected function getDefaultCriteria(): array
    {
        return [];
    }

    protected function getDefaultOrdering(): array
    {
        return ['username' => 'ASC'];
    }

    /**
     * @inheritDoc
     */
    public function supports($item): bool
    {
        if ($item instanceof User) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function toExplorerItem(mixed $item): ?ExplorerItemInterface
    {
        if ($item instanceof User) {
            return new UserExplorerItem($item, $this->urlGenerator);
        }
        throw new \InvalidArgumentException('Explorer item must be instance of ' . User::class);
    }
}
