<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Field;

use RZ\Roadiz\EntityGenerator\Attribute\AttributeGenerator;
use RZ\Roadiz\EntityGenerator\Attribute\AttributeListGenerator;

class YamlFieldGenerator extends NonVirtualFieldGenerator
{
    protected function getSerializationAttributes(): array
    {
        $annotations = parent::getSerializationAttributes();
        if (!$this->excludeFromSerialization()) {
            $annotations[] = new AttributeGenerator('Serializer\VirtualProperty');
            $annotations[] = new AttributeGenerator('Serializer\SerializedName', [
                AttributeGenerator::wrapString($this->field->getVarName())
            ]);
            $annotations[] = new AttributeGenerator('SymfonySerializer\SerializedName', [
                'serializedName' => AttributeGenerator::wrapString($this->field->getVarName())
            ]);
            $annotations[] = new AttributeGenerator('SymfonySerializer\Groups', [
                $this->getSerializationGroups()
            ]);
            if ($this->getSerializationMaxDepth() > 0) {
                $annotations[] = new AttributeGenerator('SymfonySerializer\MaxDepth', [
                    $this->getSerializationMaxDepth()
                ]);
            }
        }
        return $annotations;
    }

    protected function getDefaultSerializationGroups(): array
    {
        $groups = parent::getDefaultSerializationGroups();
        $groups[] = 'nodes_sources_yaml';
        return $groups;
    }

    protected function isExcludingFieldFromJmsSerialization(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public function getFieldAlternativeGetter(): string
    {
        $assignation = '$this->' . $this->field->getVarName();
        return '
    /**
     * @return object|array|null
     */
' . (new AttributeListGenerator($this->getSerializationAttributes()))->generate(4) . '
    public function ' . $this->field->getGetterName() . 'AsObject()
    {
        if (null !== ' . $assignation . ') {
            return \Symfony\Component\Yaml\Yaml::parse(' . $assignation . ');
        }
        return null;
    }' . PHP_EOL;
    }
}
