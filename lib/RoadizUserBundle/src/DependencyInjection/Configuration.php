<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('roadiz_user');
        $root = $builder->getRootNode();
        $root->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('password_reset_url')
            ->defaultValue('loginResetPage')
            ->info(
                <<<EOT
Define frontend URL to redirect user to after receiving its password recovery email.
**This parameter supports Symfony routes name as well as hard-coded URLs.**
EOT
            )
            ->end()
            ->scalarNode('user_validation_url')
            ->defaultValue('http://example.test/my-account/validate')
            ->info(
                <<<EOT
Define frontend URL to redirect user to after receiving its email validation request.
**This parameter supports Symfony routes name as well as hard-coded URLs.**
EOT
            )
            ->end()
            ->integerNode('password_reset_expires_in')
            ->defaultValue(600)
            ->info(
                <<<EOT
Define password recovery expiring time in seconds.
EOT
            )
            ->end()
            ->integerNode('user_validation_expires_in')
            ->defaultValue(3600)
            ->info(
                <<<EOT
Define user validation token expiring time in seconds.
EOT
            )
            ->end()
            ->scalarNode('public_user_role_name')
            ->defaultValue('ROLE_PUBLIC_USER')
            ->end()
            ->scalarNode('passwordless_user_role_name')
            ->defaultValue('ROLE_PASSWORDLESS_USER')
            ->end()
            ->scalarNode('email_validated_role_name')
            ->defaultValue('ROLE_EMAIL_VALIDATED')
            ->end();

        return $builder;
    }
}
