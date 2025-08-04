<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Explorer;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

final readonly class ExplorerProviderLocator
{
    public function __construct(
        #[AutowireLocator('roadiz.explorer_provider')]
        private ContainerInterface $explorerProviders,
    ) {
    }

    /**
     * @param class-string<ExplorerProviderInterface> $providerClass
     */
    public function getProvider(string $providerClass): ExplorerProviderInterface
    {
        if ($this->explorerProviders->has($providerClass)) {
            return $this->explorerProviders->get($providerClass);
        }

        return new $providerClass();
    }
}
