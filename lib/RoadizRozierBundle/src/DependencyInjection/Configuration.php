<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Themes\Rozier\Forms\Node\AddNodeType;
use Themes\Rozier\Forms\NodeType;

final class Configuration implements ConfigurationInterface
{
    #[\Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('roadiz_rozier');
        $builder->getRootNode()
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('node_form')->defaultValue(NodeType::class)->end()
                ->scalarNode('theme_dir')->defaultValue(
                    'vendor/roadiz/rozier/src'
                )->info('Relative path to Rozier theme sources from project directory.')->end()
                ->scalarNode('add_node_form')->defaultValue(AddNodeType::class)->end()
                ->arrayNode('entries')
                    ->defaultValue([])
                    ->info('Rozier backoffice default menu entries.')
                    ->prototype('array')
                    ->children()
                        ->scalarNode('name')->isRequired()->end()
                        ->scalarNode('route')->defaultNull()->end()
                        ->scalarNode('path')->defaultNull()->end()
                        ->scalarNode('icon')->isRequired()->end()
                        ->arrayNode('roles')
                            ->prototype('scalar')
                            ->defaultNull()
                            ->end()
                        ->end() // roles
                        ->arrayNode('subentries')
                            ->defaultValue([])
                            ->prototype('array')
                            ->children()
                                ->scalarNode('name')->isRequired()->end()
                                ->scalarNode('route')->defaultNull()->end()
                                ->scalarNode('path')->defaultNull()->end()
                                ->scalarNode('icon')->isRequired()->end()
                                ->arrayNode('roles')
                                    ->prototype('scalar')
                                    ->defaultNull()
                                    ->end()
                                ->end() // roles
                            ->end()
                            ->end()
                        ->end() // subentries
                    ->end()
                    ->end()
                ->end() // entries
            ->end()
            ->append($this->addOpenIdNode())
            ->append($this->addCsvNode())
        ;

        return $builder;
    }

    protected function addOpenIdNode(): NodeDefinition
    {
        $builder = new TreeBuilder('open_id');
        $node = $builder->getRootNode();
        $node->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('verify_user_info')
                    ->defaultTrue()
                    ->info(<<<EOD
Verify User info in JWT at each login
EOD
                    )
                ->end()
                ->booleanNode('force_ssl_on_redirect_uri')
                    ->defaultTrue()
                    ->info(<<<EOD
Force generating redirect uri with https scheme. (required by some OpenID providers)
EOD
                    )
                ->end()
                ->scalarNode('discovery_url')
                    ->defaultValue('')
                    ->info(<<<EOD
Standard OpenID autodiscovery URL, required to enable OpenId login in Roadiz CMS.
EOD
                    )
                ->end()
                ->scalarNode('hosted_domain')
                    ->defaultNull()
                    ->info(<<<EOD
For public identity providers (such as Google), restrict users emails by their domain.
EOD
                    )
                ->end()
                ->scalarNode('oauth_client_id')
                    ->defaultNull()
                    ->info(<<<EOD
OpenID identity provider OAuth2 client ID
EOD
                    )
                ->end()
                ->scalarNode('oauth_client_secret')
                    ->defaultNull()
                    ->info(<<<EOD
OpenID identity provider OAuth2 client secret
EOD
                    )
                ->end()
                ->scalarNode('openid_username_claim')
                    ->defaultValue('email')
                    ->info(<<<EOD
OpenID identity provider identifier claim field
EOD
                    )
                ->end()
                ->booleanNode('requires_local_user')
                    ->defaultValue('true')
                    ->info(<<<EOD
A local account must exists for each OpenID user.
EOD
                    )
                ->end()
                ->arrayNode('scopes')
                    ->prototype('scalar')
                    ->defaultValue([])
                    ->info(<<<EOD
Scopes requested during OpenId authentication process.
EOD
                    )
                    ->end()
                ->end()
                ->arrayNode('granted_roles')
                    ->prototype('scalar')
                    ->defaultValue(['ROLE_USER'])
                    ->info(<<<EOD
Roles granted to user logged in with OpenId authentication process. Only when local users are not required, creating virtual users.
EOD
                    )
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    protected function addCsvNode(): NodeDefinition
    {
        $builder = new TreeBuilder('csv_encoder_options');
        $node = $builder->getRootNode();
        $builder->getRootNode()->addDefaultsIfNotSet()
            ->info('https://symfony.com/doc/6.4/serializer/encoders.html#the-csvencoder')
            ->children()
                ->scalarNode('csv_delimiter')
                    ->defaultValue(',')
                ->end()
                ->scalarNode('csv_enclosure')
                    ->defaultValue('"')
                ->end()
                ->scalarNode('csv_escape_char')
                    ->defaultValue('')
                ->end()
                ->scalarNode('csv_end_of_line')
                    ->defaultValue("\n")
                ->end()
                ->scalarNode('csv_key_separator')
                    ->defaultValue('.')
                ->end()
                ->booleanNode('output_utf8_bom')
                    ->defaultFalse()
                ->end()
            ->end();

        return $node;
    }
}
