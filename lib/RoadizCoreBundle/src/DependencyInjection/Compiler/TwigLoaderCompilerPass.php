<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TwigLoaderCompilerPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        if ($container->has('twig.loader.native_filesystem')) {
            $definition = $container->findDefinition('twig.loader.native_filesystem');
            $definition->addMethodCall(
                'prependPath',
                [ realpath(dirname(__DIR__) . '/../../templates/ApiPlatformBundle'), 'ApiPlatform' ]
            );
        }
    }
}
