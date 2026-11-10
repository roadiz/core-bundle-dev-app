<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\DependencyInjection\Compiler;

use RZ\Roadiz\CoreBundle\Serializer\Normalizer\EnrichingItemNormalizer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Former per-entity normalizer ids are now aliases of EnrichingItemNormalizer services.
 * Decorating an alias would only re-point the alias and leave the decorator out of the
 * serializer chain, so services decorating them are moved onto the aliased service.
 */
final class DeprecatedNormalizerDecorationCompilerPass implements CompilerPassInterface
{
    private const array TARGETS = [
        EnrichingItemNormalizer::class,
        'roadiz_core.serializer.normalizer.enriching_item.json',
    ];

    #[\Override]
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $id => $definition) {
            $decorated = $definition->getDecoratedService();
            if (null === $decorated || !$container->hasAlias($decorated[0])) {
                continue;
            }
            $alias = $container->getAlias($decorated[0]);
            $target = (string) $alias;
            if (!$alias->isDeprecated() || !\in_array($target, self::TARGETS, true)) {
                continue;
            }

            trigger_deprecation('roadiz/core-bundle', '2.7.51', 'Service "%s" decorates deprecated "%s", decorate "%s" or write a NormalizationEnricherInterface instead.', $id, $decorated[0], $target);
            $definition->setDecoratedService($target, $decorated[1], $decorated[2], $decorated[3] ?? ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE);
        }
    }
}
