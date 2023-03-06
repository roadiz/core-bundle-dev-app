<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use ApiPlatform\Metadata\ApiFilter;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter as BaseFilter;

trait AttributeValueTrait
{
    #[
        ORM\ManyToOne(targetEntity: AttributeInterface::class, fetch: "EAGER", inversedBy: "attributeValues"),
        ORM\JoinColumn(name: "attribute_id", referencedColumnName: "id", onDelete: "CASCADE"),
        Serializer\Groups(["attribute", "node", "nodes_sources"]),
        Serializer\Type("RZ\Roadiz\CoreBundle\Entity\Attribute"),
        ApiFilter(BaseFilter\SearchFilter::class, properties: [
            "attribute.id" => "exact",
            "attribute.code" => "exact",
            "attribute.type" => "exact",
            "attribute.group" => "exact",
            "attribute.group.canonicalName" => "exact",
        ]),
        ApiFilter(BaseFilter\BooleanFilter::class, properties: [
            "attribute.visible",
            "attribute.searchable"
        ])
    ]
    protected ?AttributeInterface $attribute = null;

    /**
     * @var Collection<AttributeValueTranslationInterface>
     */
    #[
        ORM\OneToMany(
            mappedBy: "attributeValue",
            targetEntity: AttributeValueTranslationInterface::class,
            cascade: ["persist", "remove"],
            fetch: "EAGER",
            orphanRemoval: true
        ),
        Serializer\Groups(["attribute", "node", "nodes_sources"]),
        Serializer\Type("ArrayCollection<RZ\Roadiz\CoreBundle\Model\AttributeValueTranslationInterface>"),
        Serializer\Accessor(getter: "getAttributeValueTranslations", setter: "setAttributeValueTranslations")
    ]
    protected Collection $attributeValueTranslations;

    /**
     * @return AttributeInterface
     */
    public function getAttribute(): ?AttributeInterface
    {
        return $this->attribute;
    }

    /**
     * @param AttributeInterface $attribute
     *
     * @return mixed
     */
    public function setAttribute(AttributeInterface $attribute)
    {
        $this->attribute = $attribute;
        return $this;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->getAttribute()->getType();
    }

    /**
     * @return Collection<AttributeValueTranslationInterface>
     */
    public function getAttributeValueTranslations(): Collection
    {
        return $this->attributeValueTranslations;
    }

    /**
     * @param Collection $attributeValueTranslations
     *
     * @return mixed
     */
    public function setAttributeValueTranslations(Collection $attributeValueTranslations)
    {
        $this->attributeValueTranslations = $attributeValueTranslations;
        /** @var AttributeValueTranslationInterface $attributeValueTranslation */
        foreach ($this->attributeValueTranslations as $attributeValueTranslation) {
            $attributeValueTranslation->setAttributeValue($this);
        }
        return true;
    }

    /**
     * @param TranslationInterface $translation
     *
     * @return AttributeValueTranslationInterface
     */
    public function getAttributeValueTranslation(TranslationInterface $translation): ?AttributeValueTranslationInterface
    {
        return $this->getAttributeValueTranslations()
            ->filter(function (AttributeValueTranslationInterface $attributeValueTranslation) use ($translation) {
                if ($attributeValueTranslation->getTranslation() === $translation) {
                    return true;
                }
                return false;
            })
            ->first() ?: null;
    }
}
