<?php
declare(strict_types=1);

namespace tests\mocks;

use Doctrine\Common\Collections\ArrayCollection;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeResolverInterface;

trait NodeTypeAwareTrait
{
    protected function getMockNodeType()
    {
        $mockNodeType = $this->newMockInstance(NodeTypeInterface::class);
        $mockNodeType->getMockController()->getFields = function() {
            return new ArrayCollection([
                (new NodeTypeField())
                    ->setName('foo_datetime')
                    ->setTypeName('datetime')
                    ->setDoctrineType('datetime')
                    ->setSerializationGroups([
                        'nodes_sources',
                        'nodes_sources_default',
                        'foo_datetime'
                    ])
                    ->setVirtual(false)
                    ->setLabel('Foo DateTime field')
                    ->setIndexed(true),
                (new NodeTypeField())
                    ->setName('foo')
                    ->setTypeName('string')
                    ->setVirtual(false)
                    ->setSerializationMaxDepth(1)
                    ->setLabel('Foo field')
                    ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                    ->setIndexed(false),
                (new NodeTypeField())
                    ->setName('fooIndexed')
                    ->setTypeName('string')
                    ->setVirtual(false)
                    ->setSerializationMaxDepth(1)
                    ->setLabel('Foo indexed field')
                    ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                    ->setIndexed(true),
                (new NodeTypeField())
                    ->setName('boolIndexed')
                    ->setTypeName('bool')
                    ->setDoctrineType('boolean')
                    ->setVirtual(false)
                    ->setSerializationMaxDepth(1)
                    ->setLabel('Bool indexed field')
                    ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                    ->setIndexed(true),
                (new NodeTypeField())
                    ->setName('foo_markdown')
                    ->setTypeName('markdown')
                    ->setDoctrineType('text')
                    ->setVirtual(false)
                    ->setSerializationMaxDepth(1)
                    ->setLabel('Foo markdown field')
                    ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                    ->setDefaultValues("allow_h2: false\r\nallow_h3: false\r\nallow_h4: false\r\nallow_h5: false\r\nallow_h6: false\r\nallow_bold: true\r\nallow_italic: true\r\nallow_blockquote: false\r\nallow_image: false\r\nallow_list: false\r\nallow_nbsp: true\r\nallow_nb_hyphen: true\r\nallow_return: true\r\nallow_link: false\r\nallow_hr: false\r\nallow_preview: true")
                    ->setIndexed(false),
                (new NodeTypeField())
                    ->setName('foo_markdown_excluded')
                    ->setTypeName('markdown')
                    ->setDoctrineType('text')
                    ->setVirtual(false)
                    ->setExcludedFromSerialization(true)
                    ->setLabel('Foo excluded markdown field')
                    ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                    ->setDefaultValues("allow_h2: false\r\nallow_h3: false\r\nallow_h4: false\r\nallow_h5: false\r\nallow_h6: false\r\nallow_bold: true\r\nallow_italic: true\r\nallow_blockquote: false\r\nallow_image: false\r\nallow_list: false\r\nallow_nbsp: true\r\nallow_nb_hyphen: true\r\nallow_return: true\r\nallow_link: false\r\nallow_hr: false\r\nallow_preview: true")
                    ->setIndexed(false),
                (new NodeTypeField())
                    ->setName('foo_decimal_excluded')
                    ->setTypeName('decimal')
                    ->setDoctrineType('decimal')
                    ->setVirtual(false)
                    ->setSerializationExclusionExpression('object.foo == \'test\'')
                    ->setLabel('Foo expression excluded decimal')
                    ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                    ->setIndexed(true),
                (new NodeTypeField())
                    ->setName('single_event_reference')
                    ->setTypeName('many_to_one')
                    ->setVirtual(false)
                    ->setLabel("Référence à l'événement")
                    ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                    ->setDefaultValues("# Entity class name\r\nclassname: \\App\\Entity\\Base\\Event\r\n# Displayable is the method used to display entity name\r\ndisplayable: getName\r\n# Same as Displayable but for a secondary information\r\nalt_displayable: getSortingFirstDateTime\r\n# Same as Displayable but for a secondary information\r\nthumbnail: getMainDocument\r\n# Searchable entity fields\r\nsearchable:\r\n    - name\r\n    - slug\r\n# This order will only be used for explorer\r\norderBy:\r\n    - field: sortingLastDateTime\r\n      direction: DESC")
                    ->setIndexed(false),
                (new NodeTypeField())
                    ->setName('event_references')
                    ->setTypeName('many_to_many')
                    ->setVirtual(false)
                    ->setLabel("Remontée d'événements manuelle")
                    ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                    ->setDefaultValues("# Entity class name\r\nclassname: \\App\\Entity\\Base\\Event\r\n# Displayable is the method used to display entity name\r\ndisplayable: getName\r\n# Same as Displayable but for a secondary information\r\nalt_displayable: getSortingFirstDateTime\r\n# Same as Displayable but for a secondary information\r\nthumbnail: getMainDocument\r\n# Searchable entity fields\r\nsearchable:\r\n    - name\r\n    - slug\r\n# This order will only be used for explorer\r\norderBy:\r\n    - field: sortingLastDateTime\r\n      direction: DESC")
                    ->setIndexed(false),
                (new NodeTypeField())
                    ->setName('event_references_proxied')
                    ->setTypeName('many_to_many')
                    ->setVirtual(false)
                    ->setLabel("Remontée d'événements manuelle")
                    ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                    ->setDefaultValues("# Entity class name\r\nclassname: \\App\\Entity\\Base\\Event\r\n# Displayable is the method used to display entity name\r\ndisplayable: getName\r\n# Same as Displayable but for a secondary information\r\nalt_displayable: getSortingFirstDateTime\r\n# Same as Displayable but for a secondary information\r\nthumbnail: getMainDocument\r\n# Searchable entity fields\r\nsearchable:\r\n    - name\r\n    - slug\r\n# This order will only be used for explorer\r\norderBy:\r\n    - field: sortingLastDateTime\r\n      direction: DESC\r\n# Use a proxy entity\r\nproxy:\r\n    classname: \\App\\Entity\\PositionedCity\r\n    self: nodeSource\r\n    relation: city\r\n    # This order will preserve position\r\n    orderBy:\r\n        - field: position\r\n          direction: ASC")
                    ->setIndexed(false),
                (new NodeTypeField())
                    ->setName('event_references_excluded')
                    ->setTypeName('many_to_many')
                    ->setVirtual(false)
                    ->setExcludedFromSerialization(true)
                    ->setLabel("Remontée d'événements manuelle exclue")
                    ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                    ->setDefaultValues("# Entity class name\r\nclassname: \\App\\Entity\\Base\\Event\r\n# Displayable is the method used to display entity name\r\ndisplayable: getName\r\n# Same as Displayable but for a secondary information\r\nalt_displayable: getSortingFirstDateTime\r\n# Same as Displayable but for a secondary information\r\nthumbnail: getMainDocument\r\n# Searchable entity fields\r\nsearchable:\r\n    - name\r\n    - slug\r\n# This order will only be used for explorer\r\norderBy:\r\n    - field: sortingLastDateTime\r\n      direction: DESC")
                    ->setIndexed(false),
                (new NodeTypeField())
                    ->setName('bar')
                    ->setTypeName('documents')
                    ->setSerializationMaxDepth(1)
                    ->setVirtual(true)
                    ->setLabel('Bar documents field')
                    ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                    ->setIndexed(false),
                (new NodeTypeField())
                    ->setName('the_forms')
                    ->setTypeName('custom_forms')
                    ->setVirtual(true)
                    ->setLabel('Custom forms field')
                    ->setIndexed(false),
                (new NodeTypeField())
                    ->setName('foo_bar')
                    ->setTypeName('nodes')
                    ->setVirtual(true)
                    ->setLabel('ForBar nodes field')
                    ->setDescription('Maecenas sed diam eget risus varius blandit sit amet non magna')
                    ->setIndexed(false),
                (new NodeTypeField())
                    ->setName('foo_bar_typed')
                    ->setTypeName('nodes')
                    ->setVirtual(true)
                    ->setLabel('ForBar nodes typed field')
                    ->setIndexed(false)
                    ->setDefaultValues('MockTwo'),
                (new NodeTypeField())
                    ->setName('foo_many_to_one')
                    ->setTypeName('many_to_one')
                    ->setVirtual(false)
                    ->setLabel('For many_to_one field')
                    ->setDefaultValues(<<<EOT
classname: \MyCustomEntity
displayable: getName
EOT)
                    ->setIndexed(false),
                (new NodeTypeField())
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
                (new NodeTypeField())
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
            ]);
        };
        $mockNodeType->getMockController()->getSourceEntityTableName = function() {
            return 'ns_mock';
        };
        $mockNodeType->getMockController()->getSourceEntityClassName = function() {
            return 'NSMock';
        };
        $mockNodeType->getMockController()->getName = function() {
            return 'Mock';
        };
        $mockNodeType->getMockController()->isReachable = function() {
            return true;
        };
        $mockNodeType->getMockController()->isPublishable = function() {
            return true;
        };
        return $mockNodeType;
    }

    protected function getMockNodeTypeResolver()
    {
        $mockNodeTypeResolver = $this->newMockInstance(NodeTypeResolverInterface::class);
        $test = $this;
        $mockNodeTypeResolver->getMockController()->get = function(string $nodeTypeName) use ($test) {
            $mockNodeType = $test->newMockInstance(NodeTypeInterface::class);
            $mockNodeType->getMockController()->getSourceEntityFullQualifiedClassName = function() use ($nodeTypeName) {
                return 'tests\mocks\GeneratedNodesSources\NS' . $nodeTypeName;
            };
            return $mockNodeType;
        };
        return $mockNodeTypeResolver;
    }
}
