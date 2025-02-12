<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Field;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\Property;
use Symfony\Component\String\UnicodeString;

final class DocumentsFieldGenerator extends AbstractFieldGenerator
{
    protected function addSerializationAttributes(Property|Method $property): self
    {
        parent::addSerializationAttributes($property);
        $property->addAttribute('JMS\Serializer\Annotation\VirtualProperty');
        $property->addAttribute('JMS\Serializer\Annotation\SerializedName', [
            $this->field->getVarName(),
        ]);
        $property->addAttribute('JMS\Serializer\Annotation\Type', [
            'array<'.
            (new UnicodeString($this->options['document_class']))->trimStart('\\')->toString().
            '>',
        ]);

        return $this;
    }

    protected function getDefaultSerializationGroups(): array
    {
        $groups = parent::getDefaultSerializationGroups();
        $groups[] = 'nodes_sources_documents';

        return $groups;
    }

    protected function getFieldTypeDeclaration(): string
    {
        return '?array';
    }

    protected function getFieldDefaultValueDeclaration(): Literal|string|null
    {
        return new Literal('null');
    }

    public function addFieldGetter(ClassType $classType, PhpNamespace $namespace): self
    {
        $getter = $classType->addMethod($this->field->getGetterName())
            ->setReturnType('array')
            ->addComment('@return '.$this->options['document_class'].'[]');
        $this->addSerializationAttributes($getter);
        $getter->setBody(<<<EOF
if (null === \$this->{$this->field->getVarName()}) {
    if (null !== \$this->objectManager) {
        \$this->{$this->field->getVarName()} = \$this->objectManager
            ->getRepository({$namespace->simplifyName($this->options['document_class'])}::class)
            ->findByNodeSourceAndFieldName(
                \$this,
                '{$this->field->getName()}'
            );
    } else {
        \$this->{$this->field->getVarName()} = [];
    }
}
return \$this->{$this->field->getVarName()};
EOF
        );

        return $this;
    }

    protected function addFieldSetter(ClassType $classType): self
    {
        $setter = $classType->addMethod('add'.ucfirst($this->field->getVarName()))
            ->setReturnType('static')
            ->addComment('@return $this')
            ->setPublic();

        $setter->addParameter('document')
            ->setType($this->options['document_class']);
        $setter->setBody(<<<PHP
if (null === \$this->objectManager) {
    return \$this;
}
\$nodeSourceDocument = new {$this->options['document_proxy_class']}(
    \$this,
    \$document
);
\$nodeSourceDocument->setFieldName('{$this->field->getName()}');
if (!\$this->hasNodesSourcesDocuments(\$nodeSourceDocument)) {
    \$this->objectManager->persist(\$nodeSourceDocument);
    \$this->addDocumentsByFields(\$nodeSourceDocument);
    \$this->{$this->field->getVarName()} = null;
}
return \$this;
PHP
        );

        return $this;
    }
}
