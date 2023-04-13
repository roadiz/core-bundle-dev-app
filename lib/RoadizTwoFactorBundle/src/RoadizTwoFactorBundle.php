<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle;

use RZ\Roadiz\TwoFactorBundle\DependencyInjection\Compiler\DoctrineMigrationCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RoadizTwoFactorBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new DoctrineMigrationCompilerPass());
    }
}
