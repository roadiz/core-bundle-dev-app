<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Field;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\Property;

final class YamlFieldGenerator extends NonVirtualFieldGenerator
{
    protected function addSerializationAttributes(Property|Method $property): self
    {
        parent::addSerializationAttributes($property);
        if (!$this->excludeFromSerialization()) {
            $property->addAttribute('Symfony\Component\Serializer\Attribute\SerializedName', [
                'serializedName' => $this->field->getVarName(),
            ]);
            $property->addAttribute('Symfony\Component\Serializer\Attribute\Groups', [
                $this->getSerializationGroups(),
            ]);
            if ($this->getSerializationMaxDepth() > 0) {
                $property->addAttribute('Symfony\Component\Serializer\Attribute\MaxDepth', [
                    $this->getSerializationMaxDepth(),
                ]);
            }
        }

        return $this;
    }

    protected function getDefaultSerializationGroups(): array
    {
        $groups = parent::getDefaultSerializationGroups();
        $groups[] = 'nodes_sources_yaml';

        return $groups;
    }

    protected function hasFieldAlternativeGetter(): bool
    {
        return true;
    }

    public function addFieldAlternativeGetter(ClassType $classType): self
    {
        $assignation = '$this->'.$this->field->getVarName();

        $method = $classType->addMethod($this->field->getGetterName().'AsObject')
            ->setReturnType('object|array|null')
            ->setVisibility('public')
        ;
        $this->addSerializationAttributes($method);
        $method->setBody(<<<PHP
if (null !== {$assignation}) {
    return \Symfony\Component\Yaml\Yaml::parse({$assignation});
}
return null;
PHP
        );

        return $this;
    }
}
