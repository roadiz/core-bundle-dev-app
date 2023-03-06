<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator;

use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeResolverInterface;
use RZ\Roadiz\CoreBundle\Entity\UserLogEntry;
use RZ\Roadiz\EntityGenerator\Attribute\AttributeGenerator;
use RZ\Roadiz\EntityGenerator\Attribute\AttributeListGenerator;
use RZ\Roadiz\EntityGenerator\Field\AbstractFieldGenerator;
use RZ\Roadiz\EntityGenerator\Field\CollectionFieldGenerator;
use RZ\Roadiz\EntityGenerator\Field\CustomFormsFieldGenerator;
use RZ\Roadiz\EntityGenerator\Field\DocumentsFieldGenerator;
use RZ\Roadiz\EntityGenerator\Field\ManyToManyFieldGenerator;
use RZ\Roadiz\EntityGenerator\Field\ManyToOneFieldGenerator;
use RZ\Roadiz\EntityGenerator\Field\NodesFieldGenerator;
use RZ\Roadiz\EntityGenerator\Field\NonVirtualFieldGenerator;
use RZ\Roadiz\EntityGenerator\Field\ProxiedManyToManyFieldGenerator;
use RZ\Roadiz\EntityGenerator\Field\YamlFieldGenerator;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Yaml\Yaml;

class EntityGenerator implements EntityGeneratorInterface
{
    private NodeTypeInterface $nodeType;
    private array $fieldGenerators;
    private NodeTypeResolverInterface $nodeTypeResolver;
    protected array $options;

    /**
     * @param NodeTypeInterface $nodeType
     * @param NodeTypeResolverInterface $nodeTypeResolver
     * @param array $options
     */
    public function __construct(NodeTypeInterface $nodeType, NodeTypeResolverInterface $nodeTypeResolver, array $options = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->nodeType = $nodeType;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->fieldGenerators = [];
        $this->options = $resolver->resolve($options);

        foreach ($this->nodeType->getFields() as $field) {
            $this->fieldGenerators[] = $this->getFieldGenerator($field);
        }
        $this->fieldGenerators = array_filter($this->fieldGenerators);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'use_native_json' => true,
            'use_api_platform_filters' => false,
        ]);
        $resolver->setRequired([
            'parent_class',
            'node_class',
            'translation_class',
            'document_class',
            'document_proxy_class',
            'custom_form_class',
            'custom_form_proxy_class',
            'repository_class',
            'namespace',
            'use_native_json',
            'use_api_platform_filters'
        ]);
        $resolver->setAllowedTypes('parent_class', 'string');
        $resolver->setAllowedTypes('node_class', 'string');
        $resolver->setAllowedTypes('translation_class', 'string');
        $resolver->setAllowedTypes('document_class', 'string');
        $resolver->setAllowedTypes('document_proxy_class', 'string');
        $resolver->setAllowedTypes('custom_form_class', 'string');
        $resolver->setAllowedTypes('custom_form_proxy_class', 'string');
        $resolver->setAllowedTypes('repository_class', 'string');
        $resolver->setAllowedTypes('namespace', 'string');
        $resolver->setAllowedTypes('use_native_json', 'bool');
        $resolver->setAllowedTypes('use_api_platform_filters', 'bool');

        $normalizeClassName = function (OptionsResolver $resolver, string $className) {
            return (new UnicodeString($className))->startsWith('\\') ?
                $className :
                '\\' . $className;
        };

        $resolver->setNormalizer('parent_class', $normalizeClassName);
        $resolver->setNormalizer('node_class', $normalizeClassName);
        $resolver->setNormalizer('translation_class', $normalizeClassName);
        $resolver->setNormalizer('document_class', $normalizeClassName);
        $resolver->setNormalizer('document_proxy_class', $normalizeClassName);
        $resolver->setNormalizer('custom_form_class', $normalizeClassName);
        $resolver->setNormalizer('custom_form_proxy_class', $normalizeClassName);
        $resolver->setNormalizer('repository_class', $normalizeClassName);
        $resolver->setNormalizer('namespace', $normalizeClassName);
    }

    /**
     * @param NodeTypeFieldInterface $field
     * @return AbstractFieldGenerator|null
     */
    protected function getFieldGenerator(NodeTypeFieldInterface $field): ?AbstractFieldGenerator
    {
        if ($field->isYaml()) {
            return new YamlFieldGenerator($field, $this->options);
        }
        if ($field->isCollection()) {
            return new CollectionFieldGenerator($field, $this->options);
        }
        if ($field->isCustomForms()) {
            return new CustomFormsFieldGenerator($field, $this->options);
        }
        if ($field->isDocuments()) {
            return new DocumentsFieldGenerator($field, $this->options);
        }
        if ($field->isManyToOne()) {
            return new ManyToOneFieldGenerator($field, $this->options);
        }
        if ($field->isManyToMany()) {
            $configuration = Yaml::parse($field->getDefaultValues() ?? '');
            if (
                is_array($configuration) &&
                isset($configuration['proxy']) &&
                !empty($configuration['proxy']['classname'])
            ) {
                /*
                 * Manually create a Many-to-Many relation using a proxy class
                 * for handling position for example.
                 */
                return new ProxiedManyToManyFieldGenerator($field, $this->options);
            }
            return new ManyToManyFieldGenerator($field, $this->options);
        }
        if ($field->isNodes()) {
            return new NodesFieldGenerator($field, $this->nodeTypeResolver, $this->options);
        }
        if (!$field->isVirtual()) {
            return new NonVirtualFieldGenerator($field, $this->options);
        }

        return null;
    }

    /**
     * @return string
     */
    public function getClassContent(): string
    {
        return $this->getClassHeader() .
            $this->getClassAnnotations() .
            $this->getClassAttributes() .
            $this->getClassBody();
    }

    /**
     * @return string
     */
    protected function getClassBody(): string
    {
        return 'class ' . $this->nodeType->getSourceEntityClassName() . ' extends ' . $this->options['parent_class'] . '
{' . $this->getClassProperties() .
        $this->getClassConstructor() .
        $this->getNodeTypeNameGetter() .
        $this->getNodeTypeReachableGetter() .
        $this->getNodeTypePublishableGetter() .
        $this->getClassCloneMethod() .
        $this->getClassMethods() . '
}' . PHP_EOL;
    }

    /**
     * @return string
     */
    protected function getClassHeader(): string
    {
        $useStatements = [
            'use Doctrine\Common\Collections\Collection;',
            'use JMS\Serializer\Annotation as Serializer;',
            'use Symfony\Component\Serializer\Annotation as SymfonySerializer;',
            'use Gedmo\Mapping\Annotation as Gedmo;',
            'use Doctrine\ORM\Mapping as ORM;',
        ];

        if ($this->options['use_api_platform_filters'] === true) {
            $useStatements[] = 'use ApiPlatform\Core\Annotation\ApiFilter;';
            $useStatements[] = 'use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter as OrmFilter;';
            $useStatements[] = 'use ApiPlatform\Core\Serializer\Filter\PropertyFilter;';
        }
        /*
         * BE CAREFUL, USE statements are required for field generators which
         * are using ::class syntax!
         */
        return '<?php

declare(strict_types=1);

/*
 * THIS IS A GENERATED FILE, DO NOT EDIT IT
 * IT WILL BE RECREATED AT EACH NODE-TYPE UPDATE
 */
namespace ' . ltrim($this->options['namespace'], '\\') . ';

' . implode(PHP_EOL, $useStatements) . PHP_EOL;
    }

    protected function getClassAttributes(): string
    {
        $attributeGenerators = [
            new AttributeGenerator('Gedmo\Loggable', [
                'logEntryClass' => '\RZ\Roadiz\CoreBundle\Entity\UserLogEntry::class',
            ]),
            new AttributeGenerator('ORM\Entity', [
                'repositoryClass' => $this->options['repository_class'] . '::class',
            ]),
            new AttributeGenerator('ORM\Table', [
                'name' => AttributeGenerator::wrapString($this->nodeType->getSourceEntityTableName())
            ])
        ];

        $indexes = [];
        /** @var AbstractFieldGenerator $fieldGenerator */
        foreach ($this->fieldGenerators as $fieldGenerator) {
            $indexes[] = $fieldGenerator->getFieldIndex();
        }
        $attributeGenerators = [...$attributeGenerators, ...array_filter($indexes)];

        if ($this->options['use_api_platform_filters'] === true) {
            $attributeGenerators[] = new AttributeGenerator('ApiFilter', [
                'PropertyFilter::class'
            ]);
        }

        return (new AttributeListGenerator($attributeGenerators))->generate() . PHP_EOL;
    }

    /**
     * @return string
     */
    protected function getClassAnnotations(): string
    {
        return '
/**
 * DO NOT EDIT
 * Generated custom node-source type by Roadiz.
 */' . PHP_EOL;
    }

    /**
     * @return string
     */
    protected function getClassProperties(): string
    {
        $fieldsArray = [];
        /** @var AbstractFieldGenerator $fieldGenerator */
        foreach ($this->fieldGenerators as $fieldGenerator) {
            $fieldsArray[] = $fieldGenerator->getField();
        }
        $fieldsArray = array_filter($fieldsArray);

        return implode('', $fieldsArray);
    }

    /**
     * @return string
     */
    protected function getClassCloneMethod(): string
    {
        $cloneStatements = [];
        /** @var AbstractFieldGenerator $fieldGenerator */
        foreach ($this->fieldGenerators as $fieldGenerator) {
            $cloneStatements[] = trim($fieldGenerator->getCloneStatements());
        }
        $cloneStatements = array_filter($cloneStatements);

        if (count($cloneStatements) === 0) {
            return '';
        }

        $statementSeparator = PHP_EOL . PHP_EOL . AbstractFieldGenerator::TAB . AbstractFieldGenerator::TAB;
        $cloneStatementsString = implode($statementSeparator, $cloneStatements);

        return '
    public function __clone()
    {
        parent::__clone();

        ' . $cloneStatementsString . '
    }' . PHP_EOL;
    }

    /**
     * @return string
     */
    protected function getClassConstructor(): string
    {
        $constructorArray = [];
        /** @var AbstractFieldGenerator $fieldGenerator */
        foreach ($this->fieldGenerators as $fieldGenerator) {
            $constructorArray[] = $fieldGenerator->getFieldConstructorInitialization();
        }
        $constructorArray = array_filter($constructorArray);

        if (count($constructorArray) > 0) {
            return '
    public function __construct(' . $this->options['node_class'] . ' $node, ' . $this->options['translation_class'] . ' $translation)
    {
        parent::__construct($node, $translation);

        ' . implode(PHP_EOL . AbstractFieldGenerator::TAB . AbstractFieldGenerator::TAB, $constructorArray) . '
    }' . PHP_EOL;
        }

        return '';
    }

    /**
     * @return string
     */
    protected function getNodeTypeNameGetter(): string
    {
            return '
    #[
        Serializer\VirtualProperty,
        Serializer\Groups(["nodes_sources", "nodes_sources_default"]),
        Serializer\SerializedName("@type"),
        SymfonySerializer\Groups(["nodes_sources", "nodes_sources_default"]),
        SymfonySerializer\SerializedName(serializedName: "@type")
    ]
    public function getNodeTypeName(): string
    {
        return \'' . $this->nodeType->getName() . '\';
    }' . PHP_EOL;
    }

    /**
     * @return string
     */
    protected function getNodeTypeReachableGetter(): string
    {
        return '
    /**
     * $this->nodeType->isReachable() proxy.
     *
     * @return bool Does this nodeSource is reachable over network?
     */
    public function isReachable(): bool
    {
        return ' . ($this->nodeType->isReachable() ? 'true' : 'false') . ';
    }' . PHP_EOL;
    }

    /**
     * @return string
     */
    protected function getNodeTypePublishableGetter(): string
    {
        return '
    /**
     * $this->nodeType->isPublishable() proxy.
     *
     * @return bool Does this nodeSource is publishable with date and time?
     */
    public function isPublishable(): bool
    {
        return ' . ($this->nodeType->isPublishable() ? 'true' : 'false') . ';
    }' . PHP_EOL;
    }

    /**
     * @return string
     */
    protected function getClassMethods(): string
    {
        return '
    public function __toString(): string
    {
        return \'[' . $this->nodeType->getSourceEntityClassName() . '] \' . parent::__toString();
    }';
    }
}
