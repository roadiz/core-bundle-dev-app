<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use ApiPlatform\Doctrine\Orm\Filter as BaseFilter;
use ApiPlatform\Metadata\ApiFilter;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;

trait AttributeValueTrait
{
    #[ORM\ManyToOne(targetEntity: AttributeInterface::class, fetch: 'EAGER', inversedBy: 'attributeValues'),
        ORM\JoinColumn(name: 'attribute_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE'),
        ApiFilter(BaseFilter\SearchFilter::class, properties: [
            'attribute.id' => 'exact',
            'attribute.code' => 'exact',
            'attribute.color' => 'exact',
            'attribute.type' => 'exact',
            'attribute.group' => 'exact',
            'attribute.group.canonicalName' => 'exact',
        ]),
        ApiFilter(BaseFilter\BooleanFilter::class, properties: [
            'attribute.visible',
            'attribute.searchable',
        ]),
        ApiFilter(BaseFilter\ExistsFilter::class, properties: [
            'attribute.color',
            'attribute.group',
        ]),
        ApiFilter(BaseFilter\OrderFilter::class, properties: [
            'attribute.weight' => 'DESC',
        ])]
    protected AttributeInterface $attribute;

    /**
     * @var Collection<int, AttributeValueTranslationInterface>
     */
    #[ORM\OneToMany(
        mappedBy: 'attributeValue',
        targetEntity: AttributeValueTranslationInterface::class,
        cascade: ['persist', 'remove'],
        fetch: 'EAGER',
        orphanRemoval: true
    ),
        ApiFilter(BaseFilter\SearchFilter::class, properties: [
            'attributeValueTranslations.value' => 'partial',
        ]),
        ApiFilter(BaseFilter\RangeFilter::class, properties: [
            'attributeValueTranslations.value',
        ]),
        ApiFilter(BaseFilter\ExistsFilter::class, properties: [
            'attributeValueTranslations.value',
        ]),]
    protected Collection $attributeValueTranslations;

    public function getAttribute(): ?AttributeInterface
    {
        return $this->attribute;
    }

    public function setAttribute(AttributeInterface $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function getType(): int
    {
        return $this->getAttribute()->getType();
    }

    /**
     * @return Collection<int, AttributeValueTranslationInterface>
     */
    public function getAttributeValueTranslations(): Collection
    {
        return $this->attributeValueTranslations;
    }

    /**
     * @return static
     */
    public function setAttributeValueTranslations(Collection $attributeValueTranslations): self
    {
        $this->attributeValueTranslations = $attributeValueTranslations;
        /** @var AttributeValueTranslationInterface $attributeValueTranslation */
        foreach ($this->attributeValueTranslations as $attributeValueTranslation) {
            $attributeValueTranslation->setAttributeValue($this);
        }

        return $this;
    }

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

    public function getAttributeValueDefaultTranslation(): ?AttributeValueTranslationInterface
    {
        return $this->getAttributeValueTranslations()
            ->filter(function (AttributeValueTranslationInterface $attributeValueTranslation) {
                return $attributeValueTranslation->getTranslation()?->isDefaultTranslation() ?? false;
            })
            ->first() ?: null;
    }
}
