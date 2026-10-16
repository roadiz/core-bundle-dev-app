<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\DependencyInjection;

use RZ\Roadiz\SolrBundle\SolrFrenchStemmerEnum;
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
                ->arrayNode('schema')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->enumNode('french_stemmer')
                            ->info('Stemmer applied to text_fr field type by solr:init.')
                            ->enumFqcn(SolrFrenchStemmerEnum::class)
                            ->defaultValue(SolrFrenchStemmerEnum::MINIMAL)
                        ->end()
                        ->booleanNode('ascii_folding')
                            ->info('Add asciiFolding filter (preserving original) to localized text field types.')
                            ->defaultTrue()
                        ->end()
                        ->arrayNode('french_stemmer_overrides')
                            ->info('Word => replacement mappings applied to text_fr before stemming, lowercase and without accents (e.g. chateaux: chateau).')
                            ->useAttributeAsKey('word')
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
