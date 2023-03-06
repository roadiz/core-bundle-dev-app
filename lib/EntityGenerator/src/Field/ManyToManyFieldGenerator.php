<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Field;

use RZ\Roadiz\EntityGenerator\Attribute\AttributeGenerator;
use Symfony\Component\String\UnicodeString;

class ManyToManyFieldGenerator extends AbstractConfigurableFieldGenerator
{
    protected function getFieldAttributes(bool $exclude = false): array
    {
        $attributes = parent::getFieldAttributes($exclude);

        /*
         * Many Users have Many Groups.
         * @ManyToMany(targetEntity="Group")
         * @JoinTable(name="users_groups",
         *      joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},
         *      inverseJoinColumns={@JoinColumn(name="group_id", referencedColumnName="id")}
         */
        $entityA = (new UnicodeString($this->field->getNodeTypeName()))
            ->ascii()
            ->snake()
            ->lower()
            ->trim('-')
            ->trim('_')
            ->trim()
            ->toString()
        ;
        $entityB = $this->field->getName();
        $joinColumnParams = [
            'name' => AttributeGenerator::wrapString($entityA . '_id'),
            'referencedColumnName' => AttributeGenerator::wrapString('id'),
            'onDelete' => AttributeGenerator::wrapString('CASCADE')
        ];
        $inverseJoinColumns = [
            'name' => AttributeGenerator::wrapString($entityB . '_id'),
            'referencedColumnName' => AttributeGenerator::wrapString('id'),
            'onDelete' => AttributeGenerator::wrapString('CASCADE')
        ];

        $attributes[] = new AttributeGenerator('ORM\ManyToMany', [
            'targetEntity' => $this->getFullyQualifiedClassName() . '::class'
        ]);
        $attributes[] = new AttributeGenerator('ORM\JoinTable', [
            'name' => AttributeGenerator::wrapString($entityA . '_' . $entityB)
        ]);
        $attributes[] = new AttributeGenerator('ORM\JoinColumn', $joinColumnParams);
        $attributes[] = new AttributeGenerator('ORM\InverseJoinColumn', $inverseJoinColumns);
        if (count($this->configuration['orderBy']) > 0) {
            // use default order for Collections
            $orderBy = [];
            foreach ($this->configuration['orderBy'] as $order) {
                $orderBy[] = AttributeGenerator::wrapString($order['field']) .
                    ' => ' .
                    AttributeGenerator::wrapString($order['direction']);
            }
            $attributes[] = new AttributeGenerator('ORM\OrderBy', [
                0 => '[' . implode(', ', $orderBy) . ']'
            ]);
        }

        if ($this->options['use_api_platform_filters'] === true) {
            $attributes[] = new AttributeGenerator('ApiFilter', [
                0 => 'OrmFilter\SearchFilter::class',
                'strategy' => AttributeGenerator::wrapString('exact')
            ]);
        }

        return [
            ...$attributes,
            ...$this->getSerializationAttributes()
        ];
    }

    /**
     * @inheritDoc
     */
    public function getFieldAnnotation(): string
    {
        return '
    /**
     *' . implode(PHP_EOL . static::ANNOTATION_PREFIX, $this->getFieldAutodoc()) . '
     * @var Collection<' . $this->getFullyQualifiedClassName() . '>
     */' . PHP_EOL;
    }

    protected function getFieldTypeDeclaration(): string
    {
        return 'Collection';
    }

    /**
     * @inheritDoc
     */
    public function getFieldGetter(): string
    {
        return '
    /**
     * @return Collection<' . $this->getFullyQualifiedClassName() . '>
     */
    public function ' . $this->field->getGetterName() . '(): Collection
    {
        return $this->' . $this->field->getVarName() . ';
    }' . PHP_EOL;
    }

    /**
     * @inheritDoc
     */
    public function getFieldSetter(): string
    {
        return '
    /**
     * @param Collection<' . $this->getFullyQualifiedClassName() . '>|' . $this->getFullyQualifiedClassName() . '[] $' . $this->field->getVarName() . '
     * @return $this
     */
    public function ' . $this->field->getSetterName() . '(Collection|array $' . $this->field->getVarName() . '): static
    {
        if ($' . $this->field->getVarName() . ' instanceof \Doctrine\Common\Collections\Collection) {
            $this->' . $this->field->getVarName() . ' = $' . $this->field->getVarName() . ';
        } else {
            $this->' . $this->field->getVarName() . ' = new \Doctrine\Common\Collections\ArrayCollection($' . $this->field->getVarName() . ');
        }

        return $this;
    }' . PHP_EOL;
    }

    protected function isExcludingFieldFromJmsSerialization(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getFieldConstructorInitialization(): string
    {
        return '$this->' . $this->field->getVarName() . ' = new \Doctrine\Common\Collections\ArrayCollection();';
    }
}
