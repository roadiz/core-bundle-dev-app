<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Field;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeResolverInterface;
use Symfony\Component\String\UnicodeString;

final class NodesFieldGenerator extends AbstractFieldGenerator
{
    public function __construct(
        private readonly NodeTypeResolverInterface $nodeTypeResolver,
        NodeTypeFieldInterface $field,
        DefaultValuesResolverInterface $defaultValuesResolver,
        array $options = [],
    ) {
        parent::__construct($field, $defaultValuesResolver, $options);
    }

    public function addField(ClassType $classType, PhpNamespace $namespace): void
    {
        $this->addFieldGetter($classType, $namespace);
        $this->addFieldSetter($classType);
    }

    protected function getDefaultSerializationGroups(): array
    {
        $groups = parent::getDefaultSerializationGroups();
        $groups[] = 'nodes_sources_nodes';

        return $groups;
    }

    protected function getFieldSourcesName(): string
    {
        return $this->field->getVarName().'Sources';
    }

    protected function hasOnlyOneNodeType(): bool
    {
        if (!empty($this->field->getDefaultValues())) {
            $defaultValuesParsed = $this->field->getDefaultValuesAsArray();

            return 1 === count(array_unique($defaultValuesParsed));
        }

        return false;
    }

    protected function getRepositoryClass(): string
    {
        $defaultValuesParsed = $this->field->getDefaultValuesAsArray();
        if (count($defaultValuesParsed) > 0 && true === $this->hasOnlyOneNodeType()) {
            $nodeTypeName = trim(array_values($defaultValuesParsed)[0]);

            $nodeType = $this->nodeTypeResolver->get($nodeTypeName);
            if (null !== $nodeType) {
                $className = $nodeType->getSourceEntityFullQualifiedClassName();

                return (new UnicodeString($className))->startsWith('\\') ?
                    $className :
                    '\\'.$className;
            }
        }

        return $this->options['parent_class'];
    }

    public function addFieldGetter(ClassType $classType, PhpNamespace $namespace): self
    {
        $property = $classType->addProperty($this->getFieldSourcesName())
            ->setType('?array')
            ->setPrivate()
            ->setValue(null)
            ->addComment($this->getFieldSourcesName().' NodesSources direct field buffer.')
            ->addComment('@var '.$this->getRepositoryClass().'[]|null');

        $nodeSourceClasses = [];
        if (!empty($this->field->getDefaultValues())) {
            $defaultValuesParsed = $this->field->getDefaultValuesAsArray();
            $nodeTypes = array_map(
                fn ($nodeTypeName) => $this->nodeTypeResolver->get($nodeTypeName),
                $defaultValuesParsed
            );
            $nodeSourceClasses = array_map(
                fn ($nodeType) => '\\'.$nodeType->getSourceEntityFullQualifiedClassName().'::class',
                array_filter($nodeTypes)
            );
        }
        $repositoryClass = $this->getRepositoryClass().'::class';
        if (1 === count($nodeSourceClasses)) {
            $repositoryClass = array_shift($nodeSourceClasses);
            $nodeSourceClasses = '';
        } else {
            $nodeSourceClasses = implode(', ', $nodeSourceClasses);
        }

        $this->addFieldAutodoc($property);
        $this->addFieldAttributes($property, $namespace);

        $getter = $classType->addMethod($this->field->getGetterName().'Sources')
            ->setReturnType('array')
            ->addComment('@return '.$this->getRepositoryClass().'[]')
            ->setPublic();
        $this->addSerializationAttributes($getter);
        $getter->setBody(<<<PHP
if (null === \$this->{$this->getFieldSourcesName()}) {
    if (null !== \$this->objectManager) {
        \$this->{$this->getFieldSourcesName()} = \$this->objectManager
            ->getRepository({$repositoryClass})
            ->findByNodesSourcesAndFieldNameAndTranslation(
                \$this,
                '{$this->field->getName()}',
                [{$nodeSourceClasses}]
            );
    } else {
        \$this->{$this->getFieldSourcesName()} = [];
    }
}
return \$this->{$this->getFieldSourcesName()};
PHP
        );

        return $this;
    }

    public function addFieldSetter(ClassType $classType): self
    {
        $setter = $classType->addMethod($this->field->getSetterName().'Sources')
            ->setReturnType('static')
            ->addComment('@param '.$this->getRepositoryClass().'[]|null $'.$this->getFieldSourcesName())
            ->addComment('@return $this')
            ->setPublic();
        $setter->addParameter($this->getFieldSourcesName())
            ->setType('?array');
        $setter->setBody(<<<PHP
\$this->{$this->getFieldSourcesName()} = \${$this->getFieldSourcesName()};
return \$this;
PHP
        );

        return $this;
    }
}
