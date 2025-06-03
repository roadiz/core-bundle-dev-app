<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\NodeType\Configuration;

use RZ\Roadiz\CoreBundle\Enum\FieldType;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class NodeTypeConfiguration implements ConfigurationInterface
{
    #[\Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('node_type');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('name')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('displayName')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('color')->defaultValue('#000000')->end()
                ->integerNode('defaultTtl')->defaultValue(60)->end()
                ->scalarNode('description')->end()
                ->booleanNode('visible')->defaultTrue()->end()
                ->booleanNode('publishable')->defaultFalse()->end()
                ->booleanNode('attributable')->defaultFalse()->end()
                ->booleanNode('searchable')->defaultTrue()->end()
                ->booleanNode('sortingAttributesByWeight')->defaultFalse()->end()
                ->booleanNode('reachable')->defaultTrue()->end()
                ->booleanNode('hidingNodes')->defaultFalse()->end()
                ->booleanNode('hidingNonReachableNodes')->defaultTrue()->end()
                ->append($this->addFieldsNode())
            ->end()
        ;

        return $treeBuilder;
    }

    public function addFieldsNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('fields');

        $node = $treeBuilder->getRootNode()
            ->isRequired()
            ->arrayPrototype()
                ->children()
                    ->scalarNode('name')
                        ->info('Unique field name without spaces or special characters')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('label')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('groupName')
                        ->info('Assign a group for this field, this will generate a serialization group')
                    ->end()
                    ->scalarNode('placeholder')
                        ->info('Placeholder for enum type, displays when value is NULL.')
                    ->end()
                    ->scalarNode('description')
                        ->info('Field description for editors')
                    ->end()
                    ->scalarNode('serializationExclusionExpression')
                        ->info('Expression to exclude this field from serialization')
                    ->end()
                    ->enumNode('type')
                        ->isRequired()
                        ->values(array_map(
                            fn ($value) => preg_replace('#\.type$#', '', $value),
                            array_values(FieldType::humanValues())
                        ))
                    ->end()
                    ->integerNode('minLength')->defaultNull()->end()
                    ->integerNode('maxLength')->defaultNull()->end()
                    ->integerNode('serializationMaxDepth')->defaultNull()->end()
                    ->booleanNode('universal')->defaultFalse()->end()
                    ->booleanNode('excludeFromSearch')->defaultFalse()->end()
                    ->booleanNode('excludedFromSerialization')->defaultFalse()->end()
                    ->booleanNode('indexed')->defaultFalse()->end()
                    ->booleanNode('visible')->defaultTrue()->end()
                    ->booleanNode('expanded')->defaultFalse()->end()
                    ->booleanNode('required')->defaultFalse()->end()
                    ->variableNode('defaultValues')->end()
                    ->arrayNode('normalizationContext')
                        ->children()
                            ->arrayNode('groups')->scalarPrototype()->end()->end()
                            ->booleanNode('skip_null_value')->defaultTrue()->end()
                            ->booleanNode('jsonld_embed_context')->defaultFalse()->end()
                            ->booleanNode('enable_max_depth')->defaultTrue()->end()
                        ->end()
                    ->end()
                    ->arrayNode('serializationGroups')->scalarPrototype()->end()->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
