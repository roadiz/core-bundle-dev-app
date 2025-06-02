<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\DependencyInjection\Compiler;

use RZ\Roadiz\Documents\Renderer\ChainRenderer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DocumentRendererCompilerPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    #[\Override]
    public function process(ContainerBuilder $container): void
    {
        if ($container->has(ChainRenderer::class)) {
            $definition = $container->findDefinition(ChainRenderer::class);
            $references = $this->findAndSortTaggedServices(
                'roadiz_core.document_renderer',
                $container
            );
            foreach ($references as $reference) {
                $definition->addMethodCall(
                    'addRenderer',
                    [$reference]
                );
            }
        }
    }
}
