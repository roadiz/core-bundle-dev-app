<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Field;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\Property;
use Symfony\Component\String\UnicodeString;

final class ManyToManyFieldGenerator extends AbstractConfigurableFieldGenerator
{
    #[\Override]
    protected function getFieldProperty(ClassType $classType): Property
    {
        return $classType
            ->addProperty($this->field->getVarName())
            ->setPrivate()
            ->setType($this->getFieldTypeDeclaration());
    }

    #[\Override]
    protected function addFieldAttributes(Property $property, PhpNamespace $namespace, bool $exclude = false): self
    {
        parent::addFieldAttributes($property, $namespace, $exclude);

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
            'name' => $entityA.'_id',
            'referencedColumnName' => 'id',
            'onDelete' => 'CASCADE',
        ];
        $inverseJoinColumns = [
            'name' => $entityB.'_id',
            'referencedColumnName' => 'id',
            'onDelete' => 'CASCADE',
        ];

        $property->addAttribute(\Doctrine\ORM\Mapping\ManyToMany::class, [
            'targetEntity' => new Literal($this->getFullyQualifiedClassName().'::class'),
        ]);
        $property->addAttribute(\Doctrine\ORM\Mapping\JoinTable::class, [
            'name' => $entityA.'_'.$entityB,
        ]);
        $property->addAttribute(\Doctrine\ORM\Mapping\JoinColumn::class, $joinColumnParams);
        $property->addAttribute(\Doctrine\ORM\Mapping\InverseJoinColumn::class, $inverseJoinColumns);
        if (count($this->configuration['orderBy']) > 0) {
            // use default order for Collections
            $orderBy = [];
            foreach ($this->configuration['orderBy'] as $order) {
                $orderBy[$order['field']] = $order['direction'];
            }
            $property->addAttribute(\Doctrine\ORM\Mapping\OrderBy::class, [
                $orderBy,
            ]);
        }

        if (true === $this->options['use_api_platform_filters']) {
            $property->addAttribute(\ApiPlatform\Metadata\ApiFilter::class, [
                0 => new Literal($namespace->simplifyName(\ApiPlatform\Doctrine\Orm\Filter\SearchFilter::class).'::class'),
                'strategy' => 'exact',
            ]);
        }

        $this->addSerializationAttributes($property);

        return $this;
    }

    #[\Override]
    public function addFieldAnnotation(Property $property): self
    {
        $this->addFieldAutodoc($property);
        $property->addComment(
            '@var \Doctrine\Common\Collections\Collection<int, '.$this->getFullyQualifiedClassName().'>'
        );

        return $this;
    }

    #[\Override]
    protected function getFieldTypeDeclaration(): string
    {
        return \Doctrine\Common\Collections\Collection::class;
    }

    #[\Override]
    public function addFieldGetter(ClassType $classType, PhpNamespace $namespace): self
    {
        $classType->addMethod($this->field->getGetterName())
            ->setReturnType(\Doctrine\Common\Collections\Collection::class)
            ->setPublic()
            ->setBody('return $this->'.$this->field->getVarName().';')
            ->addComment(
                '@return '.
                $namespace->simplifyName(\Doctrine\Common\Collections\Collection::class).
                '<int, '.$this->getFullyQualifiedClassName().
                '>'
            );

        return $this;
    }

    #[\Override]
    public function addFieldSetter(ClassType $classType): self
    {
        $setter = $classType->addMethod($this->field->getSetterName())
            ->setReturnType('static')
            ->addComment(
                '@param \Doctrine\Common\Collections\Collection<int, '.$this->getFullyQualifiedClassName().
                '>|array<'.$this->getFullyQualifiedClassName().'> $'.$this->field->getVarName()
            )
            ->addComment('@return $this')
            ->setPublic();

        $setter->addParameter($this->field->getVarName())
            ->setType('\Doctrine\Common\Collections\Collection|array');

        $setter->setBody(<<<PHP
if (\${$this->field->getVarName()} instanceof \Doctrine\Common\Collections\Collection) {
    \$this->{$this->field->getVarName()} = \${$this->field->getVarName()};
} else {
    \$this->{$this->field->getVarName()} = new \Doctrine\Common\Collections\ArrayCollection(\${$this->field->getVarName()});
}
return \$this;
PHP
        );

        return $this;
    }

    #[\Override]
    public function getFieldConstructorInitialization(): string
    {
        return '$this->'.$this->field->getVarName().' = new \Doctrine\Common\Collections\ArrayCollection();';
    }
}
