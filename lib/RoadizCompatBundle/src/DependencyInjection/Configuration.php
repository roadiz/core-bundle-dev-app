<?php

declare(strict_types=1);

namespace RZ\Roadiz\CompatBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    #[\Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('roadiz_compat');
        $root = $builder->getRootNode();
        $root->append($this->addThemesNode());

        return $builder;
    }

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    protected function addThemesNode()
    {
        $builder = new TreeBuilder('themes');
        $node = $builder->getRootNode();

        $node
            ->defaultValue([])
            ->prototype('array')
            ->children()
                ->scalarNode('classname')
                    ->info('Full qualified theme class (this must start with \ character and ends with App suffix)')
                    ->isRequired()
                    ->validate()
                        ->ifTrue(fn (string $s) => 1 !== preg_match('/^\\\[a-zA-Z\\\]+App$/', trim($s)) || !class_exists($s))
                        ->thenInvalid('Theme class does not exist or classname is invalid: must start with \ character and ends with App suffix.')
                    ->end()
                ->end()
                ->scalarNode('hostname')
                    ->defaultValue('*')
                ->end()
                ->scalarNode('routePrefix')
                    ->defaultValue('')
                ->end()
            ->end()
            ->end();

        return $node;
    }
}
