<?php

declare(strict_types=1);

namespace RZ\Roadiz\CompatBundle;

use RZ\Roadiz\CompatBundle\DependencyInjection\Compiler\ThemesTranslatorPathsCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RoadizCompatBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new ThemesTranslatorPathsCompilerPass());
    }
}
