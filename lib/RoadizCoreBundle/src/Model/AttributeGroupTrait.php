<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Component\Validator\Constraints as Assert;

trait AttributeGroupTrait
{
    #[
        ORM\Column(name: 'canonical_name', type: 'string', length: 255, unique: true, nullable: false),
        Serializer\Groups(['attribute_group', 'attribute', 'node', 'nodes_sources']),
        Serializer\Type('string'),
        Assert\NotNull(),
        Assert\Length(max: 255),
        Assert\NotBlank()
    ]
    protected string $canonicalName = '';

    /**
     * @var Collection<int, AttributeInterface>
     */
    #[
        ORM\OneToMany(mappedBy: 'group', targetEntity: AttributeInterface::class),
        Serializer\Groups(['attribute_group']),
        Serializer\Type("ArrayCollection<RZ\Roadiz\CoreBundle\Entity\Attribute>")
    ]
    protected Collection $attributes;

    /**
     * @var Collection<int, AttributeGroupTranslationInterface>
     */
    #[
        ORM\OneToMany(
            mappedBy: 'attributeGroup',
            targetEntity: AttributeGroupTranslationInterface::class,
            cascade: ['all'],
            orphanRemoval: true
        ),
        Serializer\Groups(['attribute_group', 'attribute', 'node', 'nodes_sources']),
        Serializer\Type("ArrayCollection<RZ\Roadiz\CoreBundle\Entity\AttributeGroupTranslation>"),
        Serializer\Accessor(getter: 'getAttributeGroupTranslations', setter: 'setAttributeGroupTranslations')
    ]
    protected Collection $attributeGroupTranslations;

    public function getName(): ?string
    {
        if ($this->getAttributeGroupTranslations()->first()) {
            return $this->getAttributeGroupTranslations()->first()->getName();
        }

        return $this->getCanonicalName();
    }

    public function getTranslatedName(?TranslationInterface $translation): ?string
    {
        if (null === $translation) {
            return $this->getName();
        }

        $attributeGroupTranslation = $this->getAttributeGroupTranslations()->filter(
            function (AttributeGroupTranslationInterface $attributeGroupTranslation) use ($translation) {
                if ($attributeGroupTranslation->getTranslation() === $translation) {
                    return true;
                }

                return false;
            }
        );
        if ($attributeGroupTranslation->count() > 0 && '' !== $attributeGroupTranslation->first()->getName()) {
            return $attributeGroupTranslation->first()->getName();
        }

        return $this->getCanonicalName();
    }

    public function setName(?string $name): self
    {
        if (0 === $this->getAttributeGroupTranslations()->count()) {
            $this->getAttributeGroupTranslations()->add(
                $this->createAttributeGroupTranslation()->setName($name)
            );
        }

        $this->canonicalName = StringHandler::slugify($name ?? '');

        return $this;
    }

    public function getCanonicalName(): ?string
    {
        return $this->canonicalName;
    }

    /**
     * @return $this
     */
    public function setCanonicalName(?string $canonicalName): self
    {
        $this->canonicalName = StringHandler::slugify($canonicalName ?? '');

        return $this;
    }

    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    /**
     * @return $this
     */
    public function setAttributes(Collection $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function getAttributeGroupTranslations(): Collection
    {
        return $this->attributeGroupTranslations;
    }

    /**
     * @return $this
     */
    public function setAttributeGroupTranslations(Collection $attributeGroupTranslations): self
    {
        $this->attributeGroupTranslations = $attributeGroupTranslations;
        /** @var AttributeGroupTranslationInterface $attributeGroupTranslation */
        foreach ($this->attributeGroupTranslations as $attributeGroupTranslation) {
            $attributeGroupTranslation->setAttributeGroup($this);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function addAttributeGroupTranslation(AttributeGroupTranslationInterface $attributeGroupTranslation): self
    {
        if (!$this->getAttributeGroupTranslations()->contains($attributeGroupTranslation)) {
            $this->getAttributeGroupTranslations()->add($attributeGroupTranslation);
            $attributeGroupTranslation->setAttributeGroup($this);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removeAttributeGroupTranslation(AttributeGroupTranslationInterface $attributeGroupTranslation): self
    {
        if ($this->getAttributeGroupTranslations()->contains($attributeGroupTranslation)) {
            $this->getAttributeGroupTranslations()->removeElement($attributeGroupTranslation);
        }

        return $this;
    }

    abstract protected function createAttributeGroupTranslation(): AttributeGroupTranslationInterface;
}
