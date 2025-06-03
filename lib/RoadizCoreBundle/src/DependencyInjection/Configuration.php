<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\DependencyInjection;

use RZ\Roadiz\CoreBundle\Api\Model\WebResponse;
use RZ\Roadiz\CoreBundle\Controller\DefaultNodeSourceController;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const INHERITANCE_TYPE_JOINED = 'joined';
    public const INHERITANCE_TYPE_SINGLE_TABLE = 'single_table';

    #[\Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('roadiz_core');
        $root = $builder->getRootNode();

        $root->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('appNamespace')
                ->defaultValue('roadiz_app')
            ->end()
            ->scalarNode('healthCheckToken')
                ->defaultNull()
            ->end()
            ->scalarNode('appVersion')
                ->defaultValue('0.1.0')
            ->end()
            ->scalarNode('staticDomainName')
                ->defaultNull()
            ->end()
            ->scalarNode('maxVersionsShowed')
                ->defaultValue(10)
            ->end()
            ->scalarNode('helpExternalUrl')
                ->info('URL to display help button in back-office.')
                ->defaultValue('https://docs.roadiz.io')
            ->end()
            ->scalarNode('previewRequiredRoleName')
                ->info('Role name required to access preview mode.')
                ->defaultValue('ROLE_BACKEND_USER')
            ->end()
            ->scalarNode('defaultNodeSourceController')
                ->defaultValue(DefaultNodeSourceController::class)
            ->end()
            ->scalarNode('webResponseClass')
                ->defaultValue(WebResponse::class)
            ->end()
            ->booleanNode('useNativeJsonColumnType')
                ->defaultValue(true)
            ->end()
            ->booleanNode('useDocumentDto')
                ->defaultValue(false)
            ->end()
            ->booleanNode('hideRoadizVersion')
                ->defaultValue(false)
            ->end()
            ->booleanNode('useGravatar')
                ->defaultTrue()
            ->end()
            ->booleanNode('useEmailReplyTo')
                ->defaultTrue()
            ->end()
            ->scalarNode('documentsLibDir')->defaultValue(
                'vendor/roadiz/documents/src'
            )->info('Relative path to Roadiz Documents lib sources from project directory.')->end()
            ->booleanNode('useAcceptLanguageHeader')
                ->defaultValue(false)
                ->info(<<<EOT
When no information to find locale is found and "force_locale" setting is ON,
we must find translation based on Accept-Language header.
Be careful if you are using a reverse-proxy cache, YOU MUST vary on Accept-Language header and normalize it.
@see https://varnish-cache.org/docs/6.3/users-guide/increasing-your-hitrate.html#http-vary
EOT)
            ->end()
            ->booleanNode('useTypedNodeNames')
                ->defaultValue(true)
                ->info(<<<EOT
When enabled, this option will suffix each name for unreachable nodes (blocks) with
their node-type to avoid name conflicts with reachable nodes (pages).
EOT)
            ->end()
            ->append($this->addSolrNode())
            ->append($this->addInheritanceNode())
            ->append($this->addReverseProxyCacheNode())
            ->append($this->addMediasNode())
        ;

        return $builder;
    }

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    protected function addInheritanceNode()
    {
        $builder = new TreeBuilder('inheritance');
        $node = $builder->getRootNode();
        $node->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('type')
                    ->defaultValue(static::INHERITANCE_TYPE_SINGLE_TABLE)
                    ->info(<<<EOD
Doctrine inheritance strategy for creating NodesSources
classes table(s). BE CAREFUL, if you change this
setting after filling content in your website, all
node-sources data will be lost.
EOD
                    )
                    ->validate()
                    ->ifNotInArray([
                        static::INHERITANCE_TYPE_JOINED,
                        static::INHERITANCE_TYPE_SINGLE_TABLE,
                    ])
                    ->thenInvalid('The %s inheritance type is not supported ("joined", "single_table" are accepted).')
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    protected function addMediasNode()
    {
        $builder = new TreeBuilder('medias');
        $node = $builder->getRootNode();
        $node->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('unsplash_client_id')->defaultNull()->end()
            ->scalarNode('google_server_id')->defaultNull()->end()
            ->scalarNode('soundcloud_client_id')->defaultNull()->end()
            ->scalarNode('recaptcha_private_key')->defaultNull()->end()
            ->scalarNode('recaptcha_public_key')->defaultNull()->end()
            ->scalarNode('ffmpeg_path')->defaultNull()->end()
            ->end();

        return $node;
    }

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    protected function addSolrNode()
    {
        $builder = new TreeBuilder('solr');
        $node = $builder->getRootNode();

        $node->children()
                ->scalarNode('timeout')->defaultValue(3)->end()
                ->arrayNode('endpoints')
                    ->defaultValue([])
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('host')->isRequired()->end()
                            ->scalarNode('username')->end()
                            ->scalarNode('password')->end()
                            ->scalarNode('core')->isRequired()->end()
                            ->enumNode('scheme')
                                ->values(['http', 'https'])
                                ->defaultValue('http')
                            ->end()
                            ->scalarNode('port')->defaultValue(8983)->end()
                            ->scalarNode('path')->defaultValue('/')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    protected function addReverseProxyCacheNode()
    {
        $builder = new TreeBuilder('reverseProxyCache');
        $node = $builder->getRootNode();
        $node->children()
                ->arrayNode('frontend')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                    ->children()
                        ->scalarNode('host')
                            ->isRequired()
                            ->defaultValue('localhost')
                        ->end()
                        ->scalarNode('domainName')
                            ->isRequired()
                            ->defaultValue('localhost')
                        ->end()
                        ->scalarNode('timeout')->defaultValue(3)->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('cloudflare')
                    ->children()
                        ->scalarNode('version')
                            ->defaultValue('v4')
                        ->end()
                        ->scalarNode('zone')
                            ->isRequired()
                        ->end()
                        ->scalarNode('bearer')->end()
                        ->scalarNode('email')->end()
                        ->scalarNode('key')->end()
                        ->scalarNode('timeout')
                            ->defaultValue(3)
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }
}
