<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class CollectionFieldConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('collection');
        $root = $builder->getRootNode();
        $root->children()
            ->scalarNode('entry_type')
                ->isRequired()
                ->cannotBeEmpty()
                ->info('Full qualified class name for the AbstractType class.')
            ->end();

        return $builder;
    }
}
