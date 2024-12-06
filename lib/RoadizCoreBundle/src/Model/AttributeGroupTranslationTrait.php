<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use Symfony\Component\Validator\Constraints as Assert;

trait AttributeGroupTranslationTrait
{
    #[
        ORM\ManyToOne(targetEntity: "RZ\Roadiz\Core\AbstractEntities\TranslationInterface"),
        ORM\JoinColumn(name: 'translation_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE'),
        Serializer\Groups(['attribute_group', 'attribute', 'node', 'nodes_sources']),
        Serializer\Type("RZ\Roadiz\Core\AbstractEntities\TranslationInterface"),
        Serializer\Accessor(getter: 'getTranslation', setter: 'setTranslation')
    ]
    protected TranslationInterface $translation;

    #[
        ORM\Column(type: 'string', length: 255, unique: false, nullable: false),
        Serializer\Groups(['attribute_group', 'attribute', 'node', 'nodes_sources']),
        Serializer\Type('string'),
        Assert\Length(max: 255)
    ]
    protected string $name = '';

    #[
        ORM\ManyToOne(targetEntity: AttributeGroupInterface::class, cascade: ['persist'], inversedBy: 'attributeGroupTranslations'),
        ORM\JoinColumn(name: 'attribute_group_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE'),
        Serializer\Exclude
    ]
    protected AttributeGroupInterface $attributeGroup;

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName(string $value): self
    {
        $this->name = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function setTranslation(TranslationInterface $translation): self
    {
        $this->translation = $translation;

        return $this;
    }

    public function getTranslation(): TranslationInterface
    {
        return $this->translation;
    }

    public function getAttributeGroup(): AttributeGroupInterface
    {
        return $this->attributeGroup;
    }

    /**
     * @return $this
     */
    public function setAttributeGroup(AttributeGroupInterface $attributeGroup): self
    {
        $this->attributeGroup = $attributeGroup;

        return $this;
    }
}
