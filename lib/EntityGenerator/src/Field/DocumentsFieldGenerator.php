<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Field;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\Property;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;

final class DocumentsFieldGenerator extends AbstractFieldGenerator
{
    /**
     * Name of the generated DocumentDto buffer setter, also called by node-sources prefetching.
     */
    public static function getDtoSetterName(NodeTypeFieldInterface $field): string
    {
        return 'set'.ucfirst($field->getVarName()).'Dtos';
    }

    #[\Override]
    protected function addFieldAutodoc(Property $property): AbstractFieldGenerator
    {
        parent::addFieldAutodoc($property);

        if (true === $this->options['use_document_dto']) {
            $property->addComment('@var \RZ\Roadiz\CoreBundle\Model\DocumentDto[]|null');
        } else {
            $property->addComment('@var '.$this->options['document_class'].'[]|null');
        }

        return $this;
    }

    #[\Override]
    protected function getDefaultSerializationGroups(): array
    {
        $groups = parent::getDefaultSerializationGroups();
        $groups[] = 'nodes_sources_documents';

        return $groups;
    }

    #[\Override]
    protected function getFieldTypeDeclaration(): string
    {
        return '?array';
    }

    #[\Override]
    protected function getFieldDefaultValueDeclaration(): Literal
    {
        return new Literal('null');
    }

    #[\Override]
    public function addFieldGetter(ClassType $classType, PhpNamespace $namespace): self
    {
        $getter = $classType->addMethod($this->field->getGetterName())
            ->setReturnType('array');
        if (true === $this->options['use_document_dto']) {
            $getter->addComment('@return \RZ\Roadiz\CoreBundle\Model\DocumentDto[]');
            $method = 'findDocumentDtoByNodeSourceAndFieldName';
        } else {
            $getter->addComment('@return '.$this->options['document_class'].'[]');
            $method = 'findByNodeSourceAndFieldName';
        }
        $this->addSerializationAttributes($getter);
        $getter->setBody(<<<EOF
if (null === \$this->{$this->field->getVarName()}) {
    if (null !== \$this->objectManager) {
        \$this->{$this->field->getVarName()} = \$this->objectManager
            ->getRepository({$namespace->simplifyName($this->options['document_class'])}::class)
            ->{$method}(
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

    #[\Override]
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

        if (true === $this->options['use_document_dto']) {
            $this->addFieldDtoSetter($classType);
        }

        return $this;
    }

    /**
     * Fills the DocumentDto buffer, e.g. when documents are prefetched for several node-sources at once.
     */
    private function addFieldDtoSetter(ClassType $classType): void
    {
        $varName = $this->field->getVarName();
        $setter = $classType->addMethod(self::getDtoSetterName($this->field))
            ->setReturnType('static')
            ->addComment('@param \RZ\Roadiz\CoreBundle\Model\DocumentDto[]|null $'.$varName)
            ->addComment('@return $this')
            ->setPublic();
        $setter->addParameter($varName)
            ->setType('?array');
        $setter->setBody(<<<PHP
\$this->{$varName} = \${$varName};
return \$this;
PHP
        );
    }

    #[\Override]
    protected function getApiPropertyOptions(): array
    {
        return [
            'genId' => (true === $this->options['use_document_dto'] ? true : null),
        ];
    }
}
