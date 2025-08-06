<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class RoadizTwoFactorExtension extends Extension
{
    #[\Override]
    public function getAlias(): string
    {
        return 'roadiz_two_factor';
    }

    /**
     * @throws \Exception
     */
    #[\Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__).'/../config'));
        $loader->load('services.yaml');

        $container->setParameter(
            'scheb_two_factor.roadiz_totp.template',
            '@RoadizTwoFactor/Authentication/form.html.twig'
        );
    }
}
