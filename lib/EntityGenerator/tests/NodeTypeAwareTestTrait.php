<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Tests;

use Doctrine\Common\Collections\ArrayCollection;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeResolverInterface;
use Symfony\Component\Yaml\Yaml;

trait NodeTypeAwareTestTrait
{
    protected function getMockNodeType(): NodeTypeInterface
    {
        $mockNodeType = $this->createStub(NodeTypeInterface::class);
        $mockNodeType
            ->method('getFields')
            ->willReturn(
                new ArrayCollection([
                    (new SimpleNodeTypeField())
                        ->setName('foo_datetime')
                        ->setTypeName('datetime')
                        ->setDoctrineType('datetime')
                        ->setSerializationGroups([
                            'nodes_sources',
                            'nodes_sources_default',
                            'foo_datetime',
                        ])
                        ->setVirtual(false)
                        ->setLabel('Foo DateTime field')
                        ->setIndexed(true),
                    (new SimpleNodeTypeField())
                        ->setName('foo')
                        ->setTypeName('string')
                        ->setVirtual(false)
                        ->setSerializationMaxDepth(1)
                        ->setLabel('Foo field')
                        ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                        ->setIndexed(false),
                    (new SimpleNodeTypeField())
                        ->setName('fooMultiple')
                        ->setTypeName('multiple')
                        ->setDoctrineType('json')
                        ->setVirtual(false)
                        ->setLabel('Foo Multiple field')
                        ->setDefaultValues(<<<EOT
- maecenas
- eget
- risus
- varius
- blandit
- magna
EOT)
                        ->setIndexed(false),
                    (new SimpleNodeTypeField())
                        ->setName('fooIndexed')
                        ->setTypeName('string')
                        ->setVirtual(false)
                        ->setSerializationMaxDepth(1)
                        ->setLabel('Foo indexed field')
                        ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                        ->setIndexed(true),
                    (new SimpleNodeTypeField())
                        ->setName('fooRequired')
                        ->setTypeName('string')
                        ->setVirtual(false)
                        ->setSerializationMaxDepth(1)
                        ->setLabel('Foo required field')
                        ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                        ->setIndexed(false)
                        ->setRequired(true),
                    (new SimpleNodeTypeField())
                        ->setName('boolIndexed')
                        ->setTypeName('bool')
                        ->setDoctrineType('boolean')
                        ->setVirtual(false)
                        ->setSerializationMaxDepth(1)
                        ->setLabel('Bool indexed field')
                        ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                        ->setIndexed(true),
                    (new SimpleNodeTypeField())
                        ->setName('boolRequired')
                        ->setTypeName('bool')
                        ->setDoctrineType('boolean')
                        ->setVirtual(false)
                        ->setLabel('Bool required field')
                        ->setIndexed(false)
                        ->setRequired(true),
                    (new SimpleNodeTypeField())
                        ->setName('foo_markdown')
                        ->setTypeName('markdown')
                        ->setDoctrineType('text')
                        ->setVirtual(false)
                        ->setSerializationMaxDepth(1)
                        ->setLabel('Foo markdown field')
                        ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                        ->setDefaultValues("allow_h2: false\r\nallow_h3: false\r\nallow_h4: false\r\nallow_h5: false\r\nallow_h6: false\r\nallow_bold: true\r\nallow_italic: true\r\nallow_blockquote: false\r\nallow_image: false\r\nallow_list: false\r\nallow_nbsp: true\r\nallow_nb_hyphen: true\r\nallow_return: true\r\nallow_link: false\r\nallow_hr: false\r\nallow_preview: true")
                        ->setIndexed(false),
                    (new SimpleNodeTypeField())
                        ->setName('foo_markdown_excluded')
                        ->setTypeName('markdown')
                        ->setDoctrineType('text')
                        ->setVirtual(false)
                        ->setExcludedFromSerialization(true)
                        ->setLabel('Foo excluded markdown field')
                        ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                        ->setDefaultValues("allow_h2: false\r\nallow_h3: false\r\nallow_h4: false\r\nallow_h5: false\r\nallow_h6: false\r\nallow_bold: true\r\nallow_italic: true\r\nallow_blockquote: false\r\nallow_image: false\r\nallow_list: false\r\nallow_nbsp: true\r\nallow_nb_hyphen: true\r\nallow_return: true\r\nallow_link: false\r\nallow_hr: false\r\nallow_preview: true")
                        ->setIndexed(false),
                    (new SimpleNodeTypeField())
                        ->setName('foo_decimal_excluded')
                        ->setTypeName('decimal')
                        ->setDoctrineType('decimal')
                        ->setVirtual(false)
                        ->setSerializationExclusionExpression('object.foo == \'test\'')
                        ->setLabel('Foo expression excluded decimal')
                        ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                        ->setIndexed(true),
                    (new SimpleNodeTypeField())
                        ->setName('single_event_reference')
                        ->setTypeName('many_to_one')
                        ->setVirtual(false)
                        ->setLabel("Référence à l'événement")
                        ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                        ->setDefaultValues("# Entity class name\r\nclassname: \\App\\Entity\\Base\\Event\r\n# Displayable is the method used to display entity name\r\ndisplayable: getName\r\n# Same as Displayable but for a secondary information\r\nalt_displayable: getSortingFirstDateTime\r\n# Same as Displayable but for a secondary information\r\nthumbnail: getMainDocument\r\n# Searchable entity fields\r\nsearchable:\r\n    - name\r\n    - slug\r\n# This order will only be used for explorer\r\norderBy:\r\n    - field: sortingLastDateTime\r\n      direction: DESC")
                        ->setIndexed(false),
                    (new SimpleNodeTypeField())
                        ->setName('event_references')
                        ->setTypeName('many_to_many')
                        ->setVirtual(false)
                        ->setLabel("Remontée d'événements manuelle")
                        ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                        ->setDefaultValues("# Entity class name\r\nclassname: \\App\\Entity\\Base\\Event\r\n# Displayable is the method used to display entity name\r\ndisplayable: getName\r\n# Same as Displayable but for a secondary information\r\nalt_displayable: getSortingFirstDateTime\r\n# Same as Displayable but for a secondary information\r\nthumbnail: getMainDocument\r\n# Searchable entity fields\r\nsearchable:\r\n    - name\r\n    - slug\r\n# This order will only be used for explorer\r\norderBy:\r\n    - field: sortingLastDateTime\r\n      direction: DESC")
                        ->setIndexed(false),
                    (new SimpleNodeTypeField())
                        ->setName('event_references_proxied')
                        ->setTypeName('many_to_many')
                        ->setVirtual(false)
                        ->setLabel("Remontée d'événements manuelle")
                        ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                        ->setDefaultValues("# Entity class name\r\nclassname: \\App\\Entity\\Base\\Event\r\n# Displayable is the method used to display entity name\r\ndisplayable: getName\r\n# Same as Displayable but for a secondary information\r\nalt_displayable: getSortingFirstDateTime\r\n# Same as Displayable but for a secondary information\r\nthumbnail: getMainDocument\r\n# Searchable entity fields\r\nsearchable:\r\n    - name\r\n    - slug\r\n# This order will only be used for explorer\r\norderBy:\r\n    - field: sortingLastDateTime\r\n      direction: DESC\r\n# Use a proxy entity\r\nproxy:\r\n    classname: \\App\\Entity\\PositionedCity\r\n    self: nodeSource\r\n    relation: city\r\n    # This order will preserve position\r\n    orderBy:\r\n        - field: position\r\n          direction: ASC")
                        ->setIndexed(false),
                    (new SimpleNodeTypeField())
                        ->setName('foo_mtm_required')
                        ->setTypeName('many_to_many')
                        ->setVirtual(false)
                        ->setLabel('Many to many required field')
                        ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                        ->setDefaultValues("# Entity class name\r\nclassname: \\App\\Entity\\Base\\Event\r\n# Displayable is the method used to display entity name\r\ndisplayable: getName\r\n# Same as Displayable but for a secondary information\r\nalt_displayable: getSortingFirstDateTime\r\n# Same as Displayable but for a secondary information\r\nthumbnail: getMainDocument\r\n# Searchable entity fields\r\nsearchable:\r\n    - name\r\n    - slug\r\n# This order will only be used for explorer\r\norderBy:\r\n    - field: sortingLastDateTime\r\n      direction: DESC\r\n# Use a proxy entity\r\nproxy:\r\n    classname: \\App\\Entity\\PositionedCity\r\n    self: nodeSource\r\n    relation: city\r\n    # This order will preserve position\r\n    orderBy:\r\n        - field: position\r\n          direction: ASC")
                        ->setRequired(true),
                    (new SimpleNodeTypeField())
                        ->setName('event_references_excluded')
                        ->setTypeName('many_to_many')
                        ->setVirtual(false)
                        ->setExcludedFromSerialization(true)
                        ->setLabel("Remontée d'événements manuelle exclue")
                        ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                        ->setDefaultValues("# Entity class name\r\nclassname: \\App\\Entity\\Base\\Event\r\n# Displayable is the method used to display entity name\r\ndisplayable: getName\r\n# Same as Displayable but for a secondary information\r\nalt_displayable: getSortingFirstDateTime\r\n# Same as Displayable but for a secondary information\r\nthumbnail: getMainDocument\r\n# Searchable entity fields\r\nsearchable:\r\n    - name\r\n    - slug\r\n# This order will only be used for explorer\r\norderBy:\r\n    - field: sortingLastDateTime\r\n      direction: DESC")
                        ->setIndexed(false),
                    (new SimpleNodeTypeField())
                        ->setName('bar')
                        ->setTypeName('documents')
                        ->setSerializationMaxDepth(1)
                        ->setVirtual(true)
                        ->setLabel('Bar documents field')
                        ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                        ->setIndexed(false),
                    (new SimpleNodeTypeField())
                        ->setName('the_forms')
                        ->setTypeName('custom_forms')
                        ->setVirtual(true)
                        ->setLabel('Custom forms field')
                        ->setIndexed(false),
                    (new SimpleNodeTypeField())
                        ->setName('foo_bar')
                        ->setTypeName('nodes')
                        ->setVirtual(true)
                        ->setLabel('ForBar nodes field')
                        ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                        ->setIndexed(false),
                    (new SimpleNodeTypeField())
                        ->setName('foo_bar_hidden')
                        ->setTypeName('nodes')
                        ->setVirtual(true)
                        ->setExcludedFromSerialization(true)
                        ->setLabel('ForBar hidden nodes field')
                        ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                        ->setDefaultValues(Yaml::dump([
                            'Mock',
                            'MockTwo',
                        ]))
                        ->setIndexed(false),
                    (new SimpleNodeTypeField())
                        ->setName('foo_bar_typed')
                        ->setTypeName('nodes')
                        ->setVirtual(true)
                        ->setLabel('ForBar nodes typed field')
                        ->setIndexed(false)
                        ->setDefaultValues(Yaml::dump(['MockTwo'])),
                    (new SimpleNodeTypeField())
                        ->setName('layout')
                        ->setTypeName('enum')
                        ->setLabel('ForBar layout enum')
                        ->setIndexed(true)
                        ->setDefaultValues(Yaml::dump(['layout_odd', 'layout_odd_big_title', 'layout_even', 'layout_even_big_title', 'layout_media_grid'])),
                    (new SimpleNodeTypeField())
                        ->setName('foo_many_to_one')
                        ->setTypeName('many_to_one')
                        ->setVirtual(false)
                        ->setLabel('For many_to_one field')
                        ->setDefaultValues(<<<EOT
classname: \MyCustomEntity
displayable: getName
EOT)
                        ->setIndexed(false),
                    (new SimpleNodeTypeField())
                        ->setName('foo_many_to_many')
                        ->setTypeName('many_to_many')
                        ->setVirtual(false)
                        ->setLabel('For many_to_many field')
                        ->setDefaultValues(<<<EOT
classname: \MyCustomEntity
displayable: getName
orderBy:
    - field: name
      direction: asc
EOT)
                        ->setIndexed(false),
                    (new SimpleNodeTypeField())
                        ->setName('foo_many_to_many_proxied')
                        ->setTypeName('many_to_many')
                        ->setVirtual(false)
                        ->setSerializationMaxDepth(1)
                        ->setLabel('For many_to_many proxied field')
                        ->setDefaultValues(<<<EOT
classname: \MyCustomEntity
displayable: getName
orderBy:
    - field: name
      direction: asc
# Use a proxy entity
proxy:
    classname: Themes\MyTheme\Entities\PositionedCity
    self: nodeSource
    relation: city
    # This order will preserve position
    orderBy:
        - field: position
          direction: ASC
EOT)
                        ->setIndexed(false),
                ])
            );

        $mockNodeType
            ->method('getSourceEntityTableName')
            ->willReturn('ns_mock');
        $mockNodeType
            ->method('getSourceEntityClassName')
            ->willReturn('NSMock');
        $mockNodeType
            ->method('getName')
            ->willReturn('Mock');
        $mockNodeType
            ->method('isReachable')
            ->willReturn(true);
        $mockNodeType
            ->method('isPublishable')
            ->willReturn(true);

        return $mockNodeType;
    }

    protected function getMockDocumentNodeType(): NodeTypeInterface
    {
        $mockNodeType = $this->createStub(NodeTypeInterface::class);
        $mockNodeType
            ->method('getFields')
            ->willReturn(
                new ArrayCollection([
                    (new SimpleNodeTypeField())
                        ->setName('bar')
                        ->setTypeName('documents')
                        ->setSerializationMaxDepth(1)
                        ->setVirtual(true)
                        ->setLabel('Bar documents field')
                        ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                        ->setIndexed(false),
                ])
            );

        $mockNodeType
            ->method('getSourceEntityTableName')
            ->willReturn('ns_mock');
        $mockNodeType
            ->method('getSourceEntityClassName')
            ->willReturn('NSMock');
        $mockNodeType
            ->method('getName')
            ->willReturn('Mock');
        $mockNodeType
            ->method('isReachable')
            ->willReturn(true);
        $mockNodeType
            ->method('isPublishable')
            ->willReturn(true);

        return $mockNodeType;
    }

    protected function getMockNodeTypeResolver(): NodeTypeResolverInterface
    {
        $mockNodeTypeResolver = $this->createStub(NodeTypeResolverInterface::class);
        $mockNodeTypeResolver->method('get')->willReturnCallback(
            function (string $nodeTypeName): NodeTypeInterface {
                $mockNodeType = $this->createStub(NodeTypeInterface::class);
                $mockNodeType
                    ->method('getSourceEntityFullQualifiedClassName')
                    ->willReturn('tests\mocks\GeneratedNodesSources\NS'.$nodeTypeName)
                ;

                return $mockNodeType;
            }
        );

        return $mockNodeTypeResolver;
    }

    protected function getMockDefaultValuesResolver(): JoinedTableDefaultValuesResolver
    {
        return new JoinedTableDefaultValuesResolver();
    }
}
