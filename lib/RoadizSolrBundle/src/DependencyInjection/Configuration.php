<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    #[\Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('roadiz_solr');
        $root = $builder->getRootNode();

        $root
            ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('search')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->integerNode('fuzzy_proximity')
                                ->defaultValue(2)
                                ->min(0)
                                ->max(2)
                        ->end()
                        ->integerNode('fuzzy_min_term_length')
                            ->defaultValue(3)
                            ->min(0)
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
