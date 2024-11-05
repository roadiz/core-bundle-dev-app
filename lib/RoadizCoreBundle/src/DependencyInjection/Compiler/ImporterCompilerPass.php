<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\DependencyInjection\Compiler;

use RZ\Roadiz\CoreBundle\Importer\ChainImporter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ImporterCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->has(ChainImporter::class)) {
            $definition = $container->findDefinition(ChainImporter::class);
            $taggedServices = $container->findTaggedServiceIds(
                'roadiz_core.importer'
            );
            foreach ($taggedServices as $id => $tags) {
                $definition->addMethodCall(
                    'addImporter',
                    [new Reference($id)]
                );
            }
        }
    }
}
