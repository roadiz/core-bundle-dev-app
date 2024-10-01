<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Field;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\Property;
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

    public function addField(ClassType $classType): void
    {
        $this->addFieldGetter($classType);
        $this->addFieldSetter($classType);
    }

    protected function addSerializationAttributes(Property|Method $property): self
    {
        parent::addSerializationAttributes($property);
        $property->addAttribute('JMS\Serializer\Annotation\VirtualProperty');
        $property->addAttribute('JMS\Serializer\Annotation\SerializedName', [
            $this->field->getVarName()
        ]);
        $property->addAttribute('JMS\Serializer\Annotation\Type', [
            'array<' .
            (new UnicodeString($this->options['parent_class']))->trimStart('\\')->toString() .
            '>'
        ]);

        return $this;
    }

    protected function getDefaultSerializationGroups(): array
    {
        $groups = parent::getDefaultSerializationGroups();
        $groups[] = 'nodes_sources_nodes';
        return $groups;
    }

    /**
     * @return string
     */
    protected function getFieldSourcesName(): string
    {
        return $this->field->getVarName() . 'Sources';
    }
    /**
     * @return bool
     */
    protected function hasOnlyOneNodeType(): bool
    {
        if (!empty($this->field->getDefaultValues())) {
            return count(explode(',', $this->field->getDefaultValues())) === 1;
        }
        return false;
    }

    /**
     * @return string
     */
    protected function getRepositoryClass(): string
    {
        if (!empty($this->field->getDefaultValues()) && $this->hasOnlyOneNodeType() === true) {
            $nodeTypeName = trim(explode(',', $this->field->getDefaultValues())[0]);

            $nodeType = $this->nodeTypeResolver->get($nodeTypeName);
            if (null !== $nodeType) {
                $className = $nodeType->getSourceEntityFullQualifiedClassName();
                return (new UnicodeString($className))->startsWith('\\') ?
                    $className :
                    '\\' . $className;
            }
        }
        return $this->options['parent_class'];
    }

    public function addFieldGetter(ClassType $classType): self
    {
        $property = $classType->addProperty($this->getFieldSourcesName())
            ->setType('?array')
            ->setPrivate()
            ->setValue(null)
            ->addComment($this->getFieldSourcesName() . ' NodesSources direct field buffer.')
            ->addComment('@var ' . $this->getRepositoryClass() . '[]|null');

        $this->addFieldAutodoc($property);
        $this->addFieldAttributes($property, $this->isExcludingFieldFromJmsSerialization());


        $getter = $classType->addMethod($this->field->getGetterName() . 'Sources')
            ->setReturnType('array')
            ->addComment('@return ' . $this->getRepositoryClass() . '[]')
            ->setPublic();
        $this->addSerializationAttributes($getter);
        $getter->setBody(<<<PHP
if (null === \$this->{$this->getFieldSourcesName()}) {
    if (null !== \$this->objectManager) {
        \$this->{$this->getFieldSourcesName()} = \$this->objectManager
            ->getRepository({$this->getRepositoryClass()}::class)
            ->findByNodesSourcesAndFieldNameAndTranslation(
                \$this,
                '{$this->field->getName()}'
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
        $setter = $classType->addMethod($this->field->getSetterName() . 'Sources')
            ->setReturnType('static')
            ->addComment('@param ' . $this->getRepositoryClass() . '[]|null $' . $this->getFieldSourcesName())
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
