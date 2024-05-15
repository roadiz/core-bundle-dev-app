<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Field;

use RZ\Roadiz\EntityGenerator\Attribute\AttributeGenerator;
use RZ\Roadiz\EntityGenerator\Attribute\AttributeListGenerator;

class CustomFormsFieldGenerator extends AbstractFieldGenerator
{
    protected function getSerializationAttributes(): array
    {
        $attributes = parent::getSerializationAttributes();
        $attributes[] = new AttributeGenerator('Serializer\VirtualProperty');
        $attributes[] = new AttributeGenerator('Serializer\SerializedName', [
            AttributeGenerator::wrapString($this->field->getVarName())
        ]);

        return $attributes;
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

    protected function getFieldDefaultValueDeclaration(): string
    {
        return 'null';
    }

    /**
     * @inheritDoc
     */
    public function getFieldGetter(): string
    {
        return '
    /**
     * @return ' . $this->options['custom_form_class'] . '[] CustomForm array
     */
' . (new AttributeListGenerator($this->getSerializationAttributes()))->generate(4) . '
    public function ' . $this->field->getGetterName() . '(): array
    {
        if (null === $this->' . $this->field->getVarName() . ') {
            if (null !== $this->objectManager) {
                $this->' . $this->field->getVarName() . ' = $this->objectManager
                    ->getRepository(' . $this->options['custom_form_class'] . '::class)
                    ->findByNodeAndFieldName(
                        $this->getNode(),
                        \'' . $this->field->getName() . '\'
                    );
            } else {
                $this->' . $this->field->getVarName() . ' = [];
            }
        }
        return $this->' . $this->field->getVarName() . ';
    }' . PHP_EOL;
    }

    /**
     * Generate PHP setter method block.
     *
     * @return string
     */
    protected function getFieldSetter(): string
    {
        return '
    /**
     * @param ' . $this->options['custom_form_class'] . ' $customForm
     *
     * @return $this
     */
    public function add' . ucfirst($this->field->getVarName()) . '(' . $this->options['custom_form_class'] . ' $customForm): static
    {
        if (null !== $this->objectManager) {
            $nodeCustomForm = new ' . $this->options['custom_form_proxy_class'] . '(
                $this->getNode(),
                $customForm
            );
            $nodeCustomForm->setFieldName(\'' . $this->field->getName() . '\');
            $this->objectManager->persist($nodeCustomForm);
            $this->getNode()->addCustomForm($nodeCustomForm);
            $this->' . $this->field->getVarName() . ' = null;
        }
        return $this;
    }' . PHP_EOL;
    }
}
