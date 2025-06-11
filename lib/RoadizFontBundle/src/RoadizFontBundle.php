<?php

declare(strict_types=1);

namespace RZ\Roadiz\FontBundle;

use RZ\Roadiz\FontBundle\DependencyInjection\Compiler\DoctrineMigrationCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RoadizFontBundle extends Bundle
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

        $container->addCompilerPass(new DoctrineMigrationCompilerPass());
    }
}
