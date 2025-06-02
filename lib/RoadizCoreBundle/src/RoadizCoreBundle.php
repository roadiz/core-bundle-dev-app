<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle;

use RZ\Roadiz\CoreBundle\DependencyInjection\Compiler\CommonMarkCompilerPass;
use RZ\Roadiz\CoreBundle\DependencyInjection\Compiler\DoctrineMigrationCompilerPass;
use RZ\Roadiz\CoreBundle\DependencyInjection\Compiler\DocumentRendererCompilerPass;
use RZ\Roadiz\CoreBundle\DependencyInjection\Compiler\FlysystemStorageCompilerPass;
use RZ\Roadiz\CoreBundle\DependencyInjection\Compiler\ImporterCompilerPass;
use RZ\Roadiz\CoreBundle\DependencyInjection\Compiler\MediaFinderCompilerPass;
use RZ\Roadiz\CoreBundle\DependencyInjection\Compiler\NodesSourcesEntitiesPathCompilerPass;
use RZ\Roadiz\CoreBundle\DependencyInjection\Compiler\NodeWorkflowCompilerPass;
use RZ\Roadiz\CoreBundle\DependencyInjection\Compiler\PathResolverCompilerPass;
use RZ\Roadiz\CoreBundle\DependencyInjection\Compiler\RateLimitersCompilerPass;
use RZ\Roadiz\CoreBundle\DependencyInjection\Compiler\TreeWalkerDefinitionFactoryCompilerPass;
use RZ\Roadiz\CoreBundle\DependencyInjection\Compiler\TwigLoaderCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RoadizCoreBundle extends Bundle
{
    #[\Override]
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    #[\Override]
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new CommonMarkCompilerPass());
        $container->addCompilerPass(new MediaFinderCompilerPass());
        $container->addCompilerPass(new DocumentRendererCompilerPass());
        $container->addCompilerPass(new ImporterCompilerPass());
        $container->addCompilerPass(new NodeWorkflowCompilerPass());
        $container->addCompilerPass(new DoctrineMigrationCompilerPass());
        $container->addCompilerPass(new RateLimitersCompilerPass());
        $container->addCompilerPass(new NodesSourcesEntitiesPathCompilerPass());
        $container->addCompilerPass(new PathResolverCompilerPass());
        $container->addCompilerPass(new FlysystemStorageCompilerPass());
        $container->addCompilerPass(new TwigLoaderCompilerPass());
        $container->addCompilerPass(new TreeWalkerDefinitionFactoryCompilerPass());
    }
}
