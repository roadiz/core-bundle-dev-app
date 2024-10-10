<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Field;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\Property;
use Symfony\Component\String\UnicodeString;

final class ProxiedManyToManyFieldGenerator extends AbstractConfigurableFieldGenerator
{
    protected function addSerializationAttributes(Property|Method $property): self
    {
        parent::addSerializationAttributes($property);
        if ($this->excludeFromSerialization()) {
            return $this;
        }

        $property->addAttribute('JMS\Serializer\Annotation\VirtualProperty');
        $property->addAttribute('JMS\Serializer\Annotation\SerializedName', [
            $this->field->getVarName()
        ]);
        $property->addAttribute('Symfony\Component\Serializer\Attribute\SerializedName', [
            'serializedName' => $this->field->getVarName()
        ]);
        $property->addAttribute('Symfony\Component\Serializer\Attribute\Groups', [
            $this->getSerializationGroups()
        ]);
        if ($this->getSerializationMaxDepth() > 0) {
            $property->addAttribute('Symfony\Component\Serializer\Attribute\MaxDepth', [
                $this->getSerializationMaxDepth()
            ]);
        }
        return $this;
    }

    /**
     * Generate PHP property declaration block.
     */
    protected function getFieldProperty(ClassType $classType): Property
    {
        return $classType
            ->addProperty($this->getProxiedVarName())
            ->setPrivate()
            ->addComment('Buffer var to get referenced entities (documents, nodes, cforms, doctrine entities)')
            ->setType('\Doctrine\Common\Collections\Collection');
    }

    protected function addFieldAttributes(Property $property, bool $exclude = false): self
    {
        $property->addAttribute('JMS\Serializer\Annotation\Exclude');
        $property->addAttribute('Symfony\Component\Serializer\Attribute\Ignore');

        /*
         * Many Users have Many Groups.
         * @ManyToMany(targetEntity="Group")
         * @JoinTable(name="users_groups",
         *      joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},
         *      inverseJoinColumns={@JoinColumn(name="group_id", referencedColumnName="id")}
         */
        $ormParams = [
            'targetEntity' => new Literal('\\' . trim($this->getProxyClassname(), '\\') . '::class'),
            'mappedBy' => $this->configuration['proxy']['self'],
            'orphanRemoval' => true,
            'cascade' => ['persist', 'remove']
        ];

        $property->addAttribute('Doctrine\ORM\Mapping\OneToMany', $ormParams);

        if (isset($this->configuration['proxy']['orderBy']) && count($this->configuration['proxy']['orderBy']) > 0) {
            // use default order for Collections
            $orderBy = [];
            foreach ($this->configuration['proxy']['orderBy'] as $order) {
                $orderBy[$order['field']] = $order['direction'];
            }
            $property->addAttribute('Doctrine\ORM\Mapping\OrderBy', [
                $orderBy
            ]);
        }

        return $this;
    }

    public function addFieldAnnotation(Property $property): self
    {
        $property->addComment($this->field->getLabel() . '.');
        $property->addComment('@var \Doctrine\Common\Collections\Collection<int, ' . $this->getProxyClassname() . '>');
        return $this;
    }

    public function addFieldGetter(ClassType $classType): self
    {
        $classType->addMethod($this->getProxiedGetterName())
            ->setReturnType('\Doctrine\Common\Collections\Collection')
            ->setPublic()
            ->setBody('return $this->' . $this->getProxiedVarName() . ';')
            ->addComment('@return \Doctrine\Common\Collections\Collection<int, ' . $this->getProxyClassname() . '>');

        // Real getter
        $getter = $classType->addMethod($this->field->getGetterName())
            ->setPublic()
            ->setReturnType('array');
        $this->addSerializationAttributes($getter);
        $getter->setBody(<<<EOF
return \$this->{$this->getProxiedVarName()}->map(function ({$this->getProxyClassname()} \$proxyEntity) {
    return \$proxyEntity->{$this->getProxyRelationGetterName()}();
})->getValues();
EOF
        );

        return $this;
    }

    public function addFieldSetter(ClassType $classType): self
    {
        $proxySetter = $classType->addMethod($this->getProxiedSetterName())
            ->setReturnType('static')
            ->setPublic()
            ->addComment('@param \Doctrine\Common\Collections\Collection<int, ' . $this->getProxyClassname() . '> $' . $this->getProxiedVarName())
            ->addComment('@return $this')
        ;
        $proxySetter->addParameter($this->getProxiedVarName())
            ->setType('\Doctrine\Common\Collections\Collection');

        $proxySetter->setBody(<<<EOF
\$this->{$this->getProxiedVarName()} = \${$this->getProxiedVarName()};
return \$this;
EOF
        );

        $setter = $classType->addMethod($this->field->getSetterName())
            ->setReturnType('static')
            ->setPublic()
            ->addComment('@return $this')
        ;
        $setter->addParameter($this->field->getVarName())
            ->setType('\Doctrine\Common\Collections\Collection|array|null');

        $ucFieldVarName = ucwords($this->field->getVarName());

        $setter->setBody(<<<EOF
foreach (\$this->{$this->getProxiedGetterName()}() as \$item) {
    \$item->{$this->getProxySelfSetterName()}(null);
}
\$this->{$this->getProxiedVarName()}->clear();
if (null !== \${$this->field->getVarName()}) {
    \$position = 0;
    foreach (\${$this->field->getVarName()} as \$single{$ucFieldVarName}) {
        \$proxyEntity = new {$this->getProxyClassname()}();
        \$proxyEntity->{$this->getProxySelfSetterName()}(\$this);
        if (\$proxyEntity instanceof \RZ\Roadiz\Core\AbstractEntities\PositionedInterface) {
            \$proxyEntity->setPosition(++\$position);
        }
        \$proxyEntity->{$this->getProxyRelationSetterName()}(\$single{$ucFieldVarName});
        \$this->{$this->getProxiedVarName()}->add(\$proxyEntity);
        \$this->objectManager->persist(\$proxyEntity);
    }
}

return \$this;
EOF
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getFieldConstructorInitialization(): string
    {
        return '$this->' . $this->getProxiedVarName() . ' = new \Doctrine\Common\Collections\ArrayCollection();';
    }

    /**
     * @return string
     */
    protected function getProxiedVarName(): string
    {
        return $this->field->getVarName() . 'Proxy';
    }
    /**
     * @return string
     */
    protected function getProxiedSetterName(): string
    {
        return $this->field->getSetterName() . 'Proxy';
    }
    /**
     * @return string
     */
    protected function getProxiedGetterName(): string
    {
        return $this->field->getGetterName() . 'Proxy';
    }
    /**
     * @return string
     */
    protected function getProxySelfSetterName(): string
    {
        return 'set' . ucwords($this->configuration['proxy']['self']);
    }
    /**
     * @return string
     */
    protected function getProxyRelationSetterName(): string
    {
        return 'set' . ucwords($this->configuration['proxy']['relation']);
    }
    /**
     * @return string
     */
    protected function getProxyRelationGetterName(): string
    {
        return 'get' . ucwords($this->configuration['proxy']['relation']);
    }

    /**
     * @return string
     */
    protected function getProxyClassname(): string
    {
        return (new UnicodeString($this->configuration['proxy']['classname']))->startsWith('\\') ?
            $this->configuration['proxy']['classname'] :
            '\\' . $this->configuration['proxy']['classname'];
    }

    /**
     * @return string
     */
    public function getCloneStatements(): string
    {
        return <<<PHP
\${$this->getProxiedVarName()}Clone = new \Doctrine\Common\Collections\ArrayCollection();
foreach (\$this->{$this->getProxiedVarName()} as \$item) {
    \$itemClone = clone \$item;
    \$itemClone->setNodeSource(\$this);
    \${$this->getProxiedVarName()}Clone->add(\$itemClone);
    \$this->objectManager->persist(\$itemClone);
}
\$this->{$this->getProxiedVarName()} = \${$this->getProxiedVarName()}Clone;
PHP;
    }
}
