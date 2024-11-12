<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\DependencyInjection\Compiler;

use RZ\Roadiz\CoreBundle\Api\TreeWalker\TreeWalkerGenerator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class TreeWalkerDefinitionFactoryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->has(TreeWalkerGenerator::class)) {
            $definition = $container->findDefinition(TreeWalkerGenerator::class);
            $serviceIds = $container->findTaggedServiceIds(
                'roadiz_core.tree_walker_definition_factory',
            );
            foreach ($serviceIds as $serviceId => $tags) {
                foreach ($tags as $tag) {
                    if (isset($tag['classname']) && \is_string($tag['classname'])) {
                        /*
                         * TreeWalkerGenerator::addDefinitionFactoryConfiguration($classname, $serviceId, $onlyVisible = true)
                         */
                        $definition->addMethodCall(
                            'addDefinitionFactoryConfiguration',
                            [
                                $tag['classname'],
                                new Reference($serviceId),
                                $tag['onlyVisible'] ?? true,
                            ]
                        );
                    }
                }
            }
        }
    }
}
