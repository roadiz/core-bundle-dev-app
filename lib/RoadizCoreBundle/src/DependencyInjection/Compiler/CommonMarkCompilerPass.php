<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CommonMarkCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->has('roadiz_core.markdown.environments.text_converter')) {
            $definition = $container->findDefinition(
                'roadiz_core.markdown.environments.text_converter'
            );
            $taggedServices = $container->findTaggedServiceIds(
                'roadiz_core.markdown.text_converter.extension'
            );
            foreach ($taggedServices as $id => $tags) {
                $definition->addMethodCall(
                    'addExtension',
                    [new Reference($id)]
                );
            }
        }

        if ($container->has('roadiz_core.markdown.environments.text_extra_converter')) {
            $definition = $container->findDefinition(
                'roadiz_core.markdown.environments.text_extra_converter'
            );
            $taggedServices = $container->findTaggedServiceIds(
                'roadiz_core.markdown.text_extra_converter.extension'
            );
            foreach ($taggedServices as $id => $tags) {
                $definition->addMethodCall(
                    'addExtension',
                    [new Reference($id)]
                );
            }
        }

        if ($container->has('roadiz_core.markdown.environments.line_converter')) {
            $definition = $container->findDefinition(
                'roadiz_core.markdown.environments.line_converter'
            );
            $taggedServices = $container->findTaggedServiceIds(
                'roadiz_core.markdown.line_converter.extension'
            );
            foreach ($taggedServices as $id => $tags) {
                $definition->addMethodCall(
                    'addExtension',
                    [new Reference($id)]
                );
            }
        }
    }
}
