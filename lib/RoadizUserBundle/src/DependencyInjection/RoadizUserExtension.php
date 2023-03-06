<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class RoadizUserExtension extends Extension
{
    /**
     * @inheritDoc
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/../config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('roadiz_user.password_reset_url', $config['password_reset_url']);
        $container->setParameter('roadiz_user.user_validation_url', $config['user_validation_url']);
        $container->setParameter('roadiz_user.password_reset_expires_in', $config['password_reset_expires_in']);
        $container->setParameter('roadiz_user.user_validation_expires_in', $config['user_validation_expires_in']);
        $container->setParameter('roadiz_user.public_user_role_name', $config['public_user_role_name']);
        $container->setParameter('roadiz_user.email_validated_role_name', $config['email_validated_role_name']);
    }
}
