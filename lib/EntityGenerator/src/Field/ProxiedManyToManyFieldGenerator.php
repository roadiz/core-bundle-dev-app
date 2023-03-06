<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Field;

use RZ\Roadiz\EntityGenerator\Attribute\AttributeGenerator;
use RZ\Roadiz\EntityGenerator\Attribute\AttributeListGenerator;
use Symfony\Component\String\UnicodeString;

class ProxiedManyToManyFieldGenerator extends AbstractConfigurableFieldGenerator
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

    /**
     * Generate PHP property declaration block.
     */
    protected function getFieldDeclaration(): string
    {
        /*
         * Buffer var to get referenced entities (documents, nodes, cforms, doctrine entities)
         */
        return '    private Collection $' . $this->getProxiedVarName() . ';' . PHP_EOL;
    }

    protected function getFieldAttributes(bool $exclude = false): array
    {
        $attributes = [];

        $attributes[] = new AttributeGenerator('Serializer\Exclude');
        $attributes[] = new AttributeGenerator('SymfonySerializer\Ignore');

        /*
         * Many Users have Many Groups.
         * @ManyToMany(targetEntity="Group")
         * @JoinTable(name="users_groups",
         *      joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},
         *      inverseJoinColumns={@JoinColumn(name="group_id", referencedColumnName="id")}
         */
        $ormParams = [
            'targetEntity' => '\\' . trim($this->getProxyClassname(), '\\') . '::class',
            'mappedBy' => AttributeGenerator::wrapString($this->configuration['proxy']['self']),
            'orphanRemoval' => 'true',
            'cascade' => '["persist", "remove"]'
        ];

        $attributes[] = new AttributeGenerator('ORM\OneToMany', $ormParams);

        if (isset($this->configuration['proxy']['orderBy']) && count($this->configuration['proxy']['orderBy']) > 0) {
            // use default order for Collections
            $orderBy = [];
            foreach ($this->configuration['proxy']['orderBy'] as $order) {
                $orderBy[] = AttributeGenerator::wrapString($order['field']) .
                    ' => ' .
                    AttributeGenerator::wrapString($order['direction']);
            }
            $attributes[] = new AttributeGenerator('ORM\OrderBy', [
                0 => '[' . implode(', ', $orderBy) . ']'
            ]);
        }

        return $attributes;
    }


    /**
     * @inheritDoc
     */
    public function getFieldAnnotation(): string
    {
        return '
    /**
     * ' . $this->field->getLabel() . '
     *
     * @var Collection<' . $this->getProxyClassname() . '>
     */' . PHP_EOL;
    }

    /**
     * @inheritDoc
     */
    public function getFieldGetter(): string
    {
        return '
    /**
     * @return Collection<' . $this->getProxyClassname() . '>
     */
    public function ' . $this->getProxiedGetterName() . '(): Collection
    {
        return $this->' . $this->getProxiedVarName() . ';
    }

    /**
     * @return Collection
     */
' . (new AttributeListGenerator($this->getSerializationAttributes()))->generate(4) . '
    public function ' . $this->field->getGetterName() . '(): Collection
    {
        return $this->' . $this->getProxiedVarName() . '->map(function (' . $this->getProxyClassname() . ' $proxyEntity) {
            return $proxyEntity->' . $this->getProxyRelationGetterName() . '();
        });
    }' . PHP_EOL;
    }

    /**
     * @inheritDoc
     */
    public function getFieldSetter(): string
    {
        return '
    /**
     * @param Collection $' . $this->getProxiedVarName() . '
     * @Serializer\VirtualProperty()
     * @return $this
     */
    public function ' . $this->getProxiedSetterName() . '(Collection $' . $this->getProxiedVarName() . '): static
    {
        $this->' . $this->getProxiedVarName() . ' = $' . $this->getProxiedVarName() . ';

        return $this;
    }
    /**
     * @param Collection|array|null $' . $this->field->getVarName() . '
     * @return $this
     */
    public function ' . $this->field->getSetterName() . '(Collection|array|null $' . $this->field->getVarName() . ' = null): static
    {
        foreach ($this->' . $this->getProxiedGetterName() . '() as $item) {
            $item->' . $this->getProxySelfSetterName() . '(null);
        }
        $this->' . $this->getProxiedVarName() . '->clear();
        if (null !== $' . $this->field->getVarName() . ') {
            $position = 0;
            foreach ($' . $this->field->getVarName() . ' as $single' . ucwords($this->field->getVarName()) . ') {
                $proxyEntity = new ' . $this->getProxyClassname() . '();
                $proxyEntity->' . $this->getProxySelfSetterName() . '($this);
                if ($proxyEntity instanceof \RZ\Roadiz\Core\AbstractEntities\PositionedInterface) {
                    $proxyEntity->setPosition(++$position);
                }
                $proxyEntity->' . $this->getProxyRelationSetterName() . '($single' . ucwords($this->field->getVarName()) . ');
                $this->' . $this->getProxiedVarName() . '->add($proxyEntity);
                $this->objectManager->persist($proxyEntity);
            }
        }

        return $this;
    }' . PHP_EOL;
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
        return '

        $' . $this->getProxiedVarName() . 'Clone = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($this->' . $this->getProxiedVarName() . ' as $item) {
            $itemClone = clone $item;
            $itemClone->setNodeSource($this);
            $' . $this->getProxiedVarName() . 'Clone->add($itemClone);
            $this->objectManager->persist($itemClone);
        }
        $this->' . $this->getProxiedVarName() . ' = $' . $this->getProxiedVarName() . 'Clone;
        ';
    }
}
