<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle;

use RZ\Roadiz\UserBundle\DependencyInjection\Compiler\DoctrineMigrationCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RoadizUserBundle extends Bundle
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
