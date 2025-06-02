<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\Printer;
use Nette\PhpGenerator\PsrPrinter;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeResolverInterface;
use RZ\Roadiz\EntityGenerator\Field\AbstractFieldGenerator;
use RZ\Roadiz\EntityGenerator\Field\CollectionFieldGenerator;
use RZ\Roadiz\EntityGenerator\Field\CustomFormsFieldGenerator;
use RZ\Roadiz\EntityGenerator\Field\DefaultValuesResolverInterface;
use RZ\Roadiz\EntityGenerator\Field\DocumentsFieldGenerator;
use RZ\Roadiz\EntityGenerator\Field\ManyToManyFieldGenerator;
use RZ\Roadiz\EntityGenerator\Field\ManyToOneFieldGenerator;
use RZ\Roadiz\EntityGenerator\Field\NodesFieldGenerator;
use RZ\Roadiz\EntityGenerator\Field\NonVirtualFieldGenerator;
use RZ\Roadiz\EntityGenerator\Field\ProxiedManyToManyFieldGenerator;
use RZ\Roadiz\EntityGenerator\Field\YamlFieldGenerator;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\UnicodeString;

final class EntityGenerator implements EntityGeneratorInterface
{
    /**
     * @var AbstractFieldGenerator[]
     */
    private array $fieldGenerators;
    private array $options;
    private Printer $printer;

    public function __construct(
        private readonly NodeTypeInterface $nodeType,
        private readonly NodeTypeResolverInterface $nodeTypeResolver,
        private readonly DefaultValuesResolverInterface $defaultValuesResolver,
        array $options = [],
    ) {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->fieldGenerators = [];
        $this->options = $resolver->resolve($options);

        foreach ($this->nodeType->getFields() as $field) {
            $this->fieldGenerators[] = $this->getFieldGenerator($field);
        }
        $this->fieldGenerators = array_filter($this->fieldGenerators);
        $this->printer = new PsrPrinter();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'use_native_json' => true,
            'use_api_platform_filters' => false,
            'use_document_dto' => false,
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
            'use_api_platform_filters',
            'use_document_dto',
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
        $resolver->setAllowedTypes('use_document_dto', 'bool');

        $normalizeClassName = function (OptionsResolver $resolver, string $className) {
            return (new UnicodeString($className))->startsWith('\\') ?
                $className :
                '\\'.$className;
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

    private function getFieldGenerator(NodeTypeFieldInterface $field): ?AbstractFieldGenerator
    {
        if ($field->isYaml()) {
            return new YamlFieldGenerator($field, $this->defaultValuesResolver, $this->options);
        }
        if ($field->isCollection()) {
            return new CollectionFieldGenerator($field, $this->defaultValuesResolver, $this->options);
        }
        if ($field->isCustomForms()) {
            return new CustomFormsFieldGenerator($field, $this->defaultValuesResolver, $this->options);
        }
        if ($field->isDocuments()) {
            return new DocumentsFieldGenerator($field, $this->defaultValuesResolver, $this->options);
        }
        if ($field->isManyToOne()) {
            return new ManyToOneFieldGenerator($field, $this->defaultValuesResolver, $this->options);
        }
        if ($field->isManyToMany()) {
            $configuration = $field->getDefaultValuesAsArray();
            if (
                isset($configuration['proxy'])
                && !empty($configuration['proxy']['classname'])
            ) {
                /*
                 * Manually create a Many-to-Many relation using a proxy class
                 * for handling position for example.
                 */
                return new ProxiedManyToManyFieldGenerator($field, $this->defaultValuesResolver, $this->options);
            }

            return new ManyToManyFieldGenerator($field, $this->defaultValuesResolver, $this->options);
        }
        if ($field->isNodes()) {
            return new NodesFieldGenerator($this->nodeTypeResolver, $field, $this->defaultValuesResolver, $this->options);
        }
        if (!$field->isVirtual()) {
            return new NonVirtualFieldGenerator($field, $this->defaultValuesResolver, $this->options);
        }

        return null;
    }

    public function getClassContent(): string
    {
        $file = new PhpFile();
        $file->setStrictTypes();
        $file->addComment('THIS IS A GENERATED FILE, DO NOT EDIT IT.');
        $file->addComment('IT WILL BE RECREATED AT EACH NODE-TYPE UPDATE.');

        $namespace = $file
            ->addNamespace(trim($this->options['namespace'], '\\'))
            ->addUse('ApiPlatform\Metadata\ApiFilter')
            ->addUse('ApiPlatform\Metadata\ApiProperty')
            ->addUse('ApiPlatform\Serializer\Filter\PropertyFilter')
            ->addUse('ApiPlatform\Doctrine\Orm\Filter', 'Filter')
            ->addUse('Doctrine\Common\Collections\Collection')
            ->addUse($this->options['parent_class'])
            ->addUse('Doctrine\ORM\Mapping', 'ORM')
            ->addUse('Gedmo\Mapping\Annotation', 'Gedmo')
            ->addUse('RZ\Roadiz\CoreBundle\Entity\Node')
            ->addUse('RZ\Roadiz\CoreBundle\Entity\Translation')
            ->addUse('RZ\Roadiz\CoreBundle\Entity\UserLogEntry')
            ->addUse('Symfony\Component\Serializer\Attribute', 'Serializer')
        ;

        $classType = $namespace->addClass($this->nodeType->getSourceEntityClassName())
            ->setExtends($this->options['parent_class'])
            ->addComment($this->nodeType->getName().' node-source entity.')
            ->addComment($this->nodeType->getDescription() ?? '');

        $this
            ->addClassAttributes($classType, $namespace)
            ->addClassFields($classType, $namespace)
            ->addClassConstructor($classType)
            ->addClassCloneMethod($classType)
            ->addClassMethods($classType)
        ;

        return $this->printer->printFile($file);
    }

    private function addClassAttributes(ClassType $classType, PhpNamespace $namespace): self
    {
        $classType
            ->addAttribute(
                'Gedmo\Mapping\Annotation\Loggable',
                ['logEntryClass' => new Literal('UserLogEntry::class')]
            )
            ->addAttribute(
                'Doctrine\ORM\Mapping\Entity',
                ['repositoryClass' => new Literal($namespace->simplifyName($this->options['repository_class']).'::class')]
            )
            ->addAttribute(
                'Doctrine\ORM\Mapping\Table',
                ['name' => $this->nodeType->getSourceEntityTableName()]
            )
        ;

        foreach ($this->fieldGenerators as $fieldGenerator) {
            $fieldGenerator->addFieldIndex($classType);
        }

        if (true === $this->options['use_api_platform_filters']) {
            $classType->addAttribute(
                'ApiPlatform\Metadata\ApiFilter',
                [new Literal($namespace->simplifyName('\ApiPlatform\Serializer\Filter\PropertyFilter').'::class')]
            );
        }

        return $this;
    }

    private function addClassFields(ClassType $classType, PhpNamespace $namespace): self
    {
        foreach ($this->fieldGenerators as $fieldGenerator) {
            $fieldGenerator->addField($classType, $namespace);
        }

        return $this;
    }

    private function addClassCloneMethod(ClassType $classType): self
    {
        $cloneStatements = [];
        /** @var AbstractFieldGenerator $fieldGenerator */
        foreach ($this->fieldGenerators as $fieldGenerator) {
            $cloneStatements[] = trim($fieldGenerator->getCloneStatements());
        }
        $cloneStatements = array_filter($cloneStatements);

        if (0 === count($cloneStatements)) {
            return $this;
        }

        $method = $classType
            ->addMethod('__clone')
            ->setReturnType('void')
        ;

        $method->addBody('parent::__clone();');

        foreach ($cloneStatements as $cloneStatement) {
            $method->addBody('');
            $method->addBody($cloneStatement);
        }

        return $this;
    }

    private function addClassConstructor(ClassType $classType): self
    {
        $constructorStatements = [];
        foreach ($this->fieldGenerators as $fieldGenerator) {
            $constructorStatements[] = $fieldGenerator->getFieldConstructorInitialization();
        }
        $constructorStatements = array_filter($constructorStatements);

        if (count($constructorStatements) > 0) {
            $constructorMethod = $classType->addMethod('__construct');
            $constructorMethod->addParameter('node')
                ->setType($this->options['node_class']);
            $constructorMethod->addParameter('translation')
                ->setType($this->options['translation_class']);
            $constructorMethod->addBody('parent::__construct($node, $translation);');
            foreach ($constructorStatements as $constructorStatement) {
                $constructorMethod->addBody($constructorStatement);
            }
        }

        return $this;
    }

    private function addClassMethods(ClassType $classType): self
    {
        $classType->addMethod('getNodeTypeName')
            ->setReturnType('string')
            ->addAttribute('Override')
            ->addAttribute('Symfony\Component\Serializer\Attribute\Groups', [['nodes_sources', 'nodes_sources_default']])
            ->addAttribute('Symfony\Component\Serializer\Attribute\SerializedName', [
                'serializedName' => '@type',
            ])
            ->setBody('return \''.$this->nodeType->getName().'\';')
        ;

        $classType->addMethod('getNodeTypeColor')
            ->setReturnType('string')
            ->addAttribute('Override')
            ->addAttribute('Symfony\Component\Serializer\Attribute\Groups', [['node_type']])
            ->addAttribute('Symfony\Component\Serializer\Attribute\SerializedName', [
                'serializedName' => 'nodeTypeColor',
            ])
            ->setBody('return \''.$this->nodeType->getColor().'\';')
        ;

        $classType->addMethod('isReachable')
            ->addComment('$this->nodeType->isReachable() proxy.')
            ->addComment('@return bool Does this nodeSource is reachable over network?')
            ->addAttribute('Override')
            ->setReturnType('bool')
            ->setBody('return '.($this->nodeType->isReachable() ? 'true' : 'false').';')
        ;

        $classType->addMethod('isPublishable')
            ->addComment('$this->nodeType->isPublishable() proxy.')
            ->addComment('@return bool Does this nodeSource is publishable with date and time?')
            ->addAttribute('Override')
            ->setReturnType('bool')
            ->setBody('return '.($this->nodeType->isPublishable() ? 'true' : 'false').';')
        ;

        $classType->addMethod('__toString')
            ->setReturnType('string')
            ->addAttribute('Override')
            ->setBody('return \'['.$this->nodeType->getSourceEntityClassName().'] \' . parent::__toString();')
        ;

        return $this;
    }
}
