<?php

declare(strict_types=1);

namespace RZ\Roadiz\FontBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class RoadizFontExtension extends Extension
{
    public function getAlias(): string
    {
        return 'roadiz_font';
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__).'/../config'));
        $loader->load('services.yaml');
    }
}
