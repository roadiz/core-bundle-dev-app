<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class RoadizTwoFactorExtension extends Extension implements PrependExtensionInterface
{
    #[\Override]
    public function getAlias(): string
    {
        return 'roadiz_two_factor';
    }

    /**
     * Provide a sensible default for the "two_factor_login" rate limiter (and its dedicated
     * cache pool) so projects don't have to configure them themselves. A project declaring its
     * own `two_factor_login` limiter or `cache.two_factor_login_limiter` pool takes precedence
     * over these defaults (Symfony merges config sources for the same key, and this is
     * prepended first).
     */
    #[\Override]
    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('framework', [
            'rate_limiter' => [
                'two_factor_login' => [
                    'policy' => 'token_bucket',
                    'limit' => 5,
                    'rate' => ['interval' => '1 minutes', 'amount' => 5],
                    'cache_pool' => 'cache.two_factor_login_limiter',
                ],
            ],
            'cache' => [
                'pools' => [
                    'cache.two_factor_login_limiter' => null,
                ],
            ],
        ]);
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
