<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Field;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\Property;

final class CustomFormsFieldGenerator extends AbstractFieldGenerator
{
    protected function addSerializationAttributes(Property|Method $property): self
    {
        parent::addSerializationAttributes($property);
        $property->addAttribute('JMS\Serializer\Annotation\VirtualProperty');
        $property->addAttribute('JMS\Serializer\Annotation\SerializedName', [
            $this->field->getVarName()
        ]);

        return $this;
    }

    protected function getDefaultSerializationGroups(): array
    {
        $groups = parent::getDefaultSerializationGroups();
        $groups[] = 'nodes_sources_custom_forms';
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
        $method = $classType
            ->addMethod($this->field->getGetterName())
            ->setReturnType('array')
            ->setVisibility('public')
            ->addComment('@return ' . $this->options['custom_form_class'] . '[] CustomForm array')
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

    protected function addFieldSetter(ClassType $classType): self
    {
        $method = $classType
            ->addMethod('add' . ucfirst($this->field->getVarName()))
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
