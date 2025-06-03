<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Field;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\Property;

final class CustomFormsFieldGenerator extends AbstractFieldGenerator
{
    #[\Override]
    protected function addFieldAnnotation(Property $property): AbstractFieldGenerator
    {
        parent::addFieldAnnotation($property);

        $property->addComment('');
        $property->addComment('@var '.$this->options['custom_form_class'].'[]|null');

        return $this;
    }

    #[\Override]
    protected function getNormalizationContext(): array
    {
        return [
            'groups' => ['nodes_sources', 'urls'],
            ...(parent::getNormalizationContext() ?? []),
        ];
    }

    #[\Override]
    protected function getDefaultSerializationGroups(): array
    {
        $groups = parent::getDefaultSerializationGroups();
        $groups[] = 'nodes_sources_custom_forms';

        return $groups;
    }

    #[\Override]
    protected function getFieldTypeDeclaration(): string
    {
        return '?array';
    }

    #[\Override]
    protected function getFieldDefaultValueDeclaration(): Literal|string|null
    {
        return new Literal('null');
    }

    #[\Override]
    public function addFieldGetter(ClassType $classType, PhpNamespace $namespace): self
    {
        $method = $classType
            ->addMethod($this->field->getGetterName())
            ->setReturnType('array')
            ->setVisibility('public')
            ->addComment('@return '.$this->options['custom_form_class'].'[] CustomForm array')
        ;
        $this->addSerializationAttributes($method);

        $method->setBody(<<<EOF
if (null === \$this->{$this->field->getVarName()}) {
    if (null !== \$this->objectManager) {
        \$this->{$this->field->getVarName()} = \$this->objectManager
            ->getRepository({$namespace->simplifyName($this->options['custom_form_class'])}::class)
            ->findByNodeAndFieldName(
                \$this->getNode(),
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
        $method = $classType
            ->addMethod('add'.ucfirst($this->field->getVarName()))
            ->setReturnType('static')
            ->setVisibility('public')
            ->addComment('@return $this')
        ;
        $method->addParameter('customForm')->setType($this->options['custom_form_class']);
        $method->setBody(<<<EOF
if (null !== \$this->objectManager) {
    \$nodeCustomForm = new {$this->options['custom_form_proxy_class']}(
        \$this->getNode(),
        \$customForm
    );
    \$nodeCustomForm->setFieldName('{$this->field->getName()}');
    \$this->objectManager->persist(\$nodeCustomForm);
    \$this->getNode()->addCustomForm(\$nodeCustomForm);
    \$this->{$this->field->getVarName()} = null;
}
return \$this;
EOF
        );

        return $this;
    }
}
