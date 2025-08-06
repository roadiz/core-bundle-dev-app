<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Explorer\Provider;

use RZ\Roadiz\CoreBundle\Entity\Setting;
use RZ\Roadiz\CoreBundle\Explorer\AbstractDoctrineExplorerProvider;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('roadiz.explorer_provider')]
final class SettingsProvider extends AbstractDoctrineExplorerProvider
{
    #[\Override]
    protected function getProvidedClassname(): string
    {
        return Setting::class;
    }

    #[\Override]
    protected function getDefaultCriteria(): array
    {
        return [];
    }

    #[\Override]
    protected function getDefaultOrdering(): array
    {
        return ['name' => 'ASC'];
    }

    #[\Override]
    public function supports(mixed $item): bool
    {
        if ($item instanceof Setting) {
            return true;
        }

        return false;
    }
}
