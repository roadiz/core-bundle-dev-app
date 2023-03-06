<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Field;

use RZ\Roadiz\EntityGenerator\Attribute\AttributeGenerator;
use RZ\Roadiz\EntityGenerator\Attribute\AttributeListGenerator;
use Symfony\Component\String\UnicodeString;

class DocumentsFieldGenerator extends AbstractFieldGenerator
{
    protected function getSerializationAttributes(): array
    {
        $annotations = parent::getSerializationAttributes();
        $annotations[] = new AttributeGenerator('Serializer\VirtualProperty');
        $annotations[] = new AttributeGenerator('Serializer\SerializedName', [
            AttributeGenerator::wrapString($this->field->getVarName())
        ]);
        $annotations[] = new AttributeGenerator('Serializer\Type', [
            AttributeGenerator::wrapString(
                'array<' .
                (new UnicodeString($this->options['document_class']))->trimStart('\\')->toString() .
                '>'
            )
        ]);

        return $annotations;
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
     * @return ' . $this->options['document_class'] . '[] Documents array
     */
' . (new AttributeListGenerator($this->getSerializationAttributes()))->generate(4) . '
    public function ' . $this->field->getGetterName() . '(): array
    {
        if (null === $this->' . $this->field->getVarName() . ') {
            if (
                null !== $this->objectManager &&
                null !== $this->getNode() &&
                null !== $this->getNode()->getNodeType()
            ) {
                $this->' . $this->field->getVarName() . ' = $this->objectManager
                    ->getRepository(' . $this->options['document_class'] . '::class)
                    ->findByNodeSourceAndField(
                        $this,
                        $this->getNode()->getNodeType()->getFieldByName("' . $this->field->getName() . '")
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
     * @param ' . $this->options['document_class'] . ' $document
     *
     * @return $this
     */
    public function add' . ucfirst($this->field->getVarName()) . '(' . $this->options['document_class'] . ' $document): static
    {
        if (
            null !== $this->objectManager &&
            null !== $this->getNode() &&
            null !== $this->getNode()->getNodeType()
        ) {
            $field = $this->getNode()->getNodeType()->getFieldByName("' . $this->field->getName() . '");
            if (null !== $field) {
                $nodeSourceDocument = new ' . $this->options['document_proxy_class'] . '(
                    $this,
                    $document,
                    $field
                );
                if (!$this->hasNodesSourcesDocuments($nodeSourceDocument)) {
                    $this->objectManager->persist($nodeSourceDocument);
                    $this->addDocumentsByFields($nodeSourceDocument);
                    $this->' . $this->field->getVarName() . ' = null;
                }
            }
        }
        return $this;
    }' . PHP_EOL;
    }
}
