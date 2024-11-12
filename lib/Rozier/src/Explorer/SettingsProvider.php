<?php

declare(strict_types=1);

namespace Themes\Rozier\Explorer;

use RZ\Roadiz\CoreBundle\Entity\Setting;
use RZ\Roadiz\CoreBundle\Explorer\AbstractDoctrineExplorerProvider;

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

    public function supports(mixed $item): bool
    {
        if ($item instanceof Setting) {
            return true;
        }

        return false;
    }
}
