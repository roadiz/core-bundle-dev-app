<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\DependencyInjection\Compiler;

use RZ\Roadiz\CoreBundle\Routing\ChainResourcePathResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;

class PathResolverCompilerPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        if ($container->has(ChainResourcePathResolver::class)) {
            $definition = $container->findDefinition(ChainResourcePathResolver::class);
            $references = $this->findAndSortTaggedServices(
                'roadiz_core.path_resolver',
                $container
            );
            foreach ($references as $reference) {
                $definition->addMethodCall(
                    'addPathResolver',
                    [$reference]
                );
            }
        }
    }
}
