<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle;

use RZ\Roadiz\RozierBundle\DependencyInjection\Compiler\JwtRoleStrategyCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RoadizRozierBundle extends Bundle
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

        $container->addCompilerPass(new JwtRoleStrategyCompilerPass());
    }
}
