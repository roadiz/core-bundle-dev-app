<?php

declare(strict_types=1);

namespace Themes\Rozier\Explorer;

use RZ\Roadiz\CoreBundle\Entity\Setting;
use RZ\Roadiz\CoreBundle\Explorer\AbstractDoctrineExplorerProvider;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerItemInterface;

final class SettingsProvider extends AbstractDoctrineExplorerProvider
{
    protected function getProvidedClassname(): string
    {
        return Setting::class;
    }

    protected function getDefaultCriteria(): array
    {
        return [];
    }

    protected function getDefaultOrdering(): array
    {
        return ['name' => 'ASC'];
    }

    /**
     * @inheritDoc
     */
    public function supports($item): bool
    {
        if ($item instanceof Setting) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function toExplorerItem(mixed $item): ?ExplorerItemInterface
    {
        if ($item instanceof Setting) {
            return new SettingExplorerItem($item, $this->urlGenerator);
        }
        throw new \InvalidArgumentException('Explorer item must be instance of ' . Setting::class);
    }
}
