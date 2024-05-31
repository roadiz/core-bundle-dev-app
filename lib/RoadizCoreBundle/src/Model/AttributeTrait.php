<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use Doctrine\Common\Collections\Collection;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\AttributeGroup;
use RZ\Roadiz\CoreBundle\Entity\AttributeTranslation;
use RZ\Roadiz\Utils\StringHandler;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

trait AttributeTrait
{
    #[
        ORM\Column(type: "string", length: 255, unique: true, nullable: false),
        Serializer\Groups(["attribute", "node", "nodes_sources"]),
        SymfonySerializer\Groups(["attribute", "node", "nodes_sources"]),
        Serializer\Type("string"),
        Assert\NotNull(),
        Assert\NotBlank(),
        Assert\Length(max: 255)
    ]
    protected string $code = '';

    #[
        ORM\Column(type: "boolean", unique: false, nullable: false, options: ["default" => false]),
        Serializer\Groups(["attribute"]),
        SymfonySerializer\Groups(["attribute"]),
        Serializer\Type("boolean")
    ]
    protected bool $searchable = false;

    #[
        ORM\Column(type: "integer", unique: false, nullable: false),
        Serializer\Groups(["attribute"]),
        SymfonySerializer\Groups(["attribute"]),
        Serializer\Type("integer")
    ]
    protected int $type = AttributeInterface::STRING_T;

    #[
        ORM\Column(type: "string", length: 7, unique: false, nullable: true),
        Serializer\Groups(["attribute", "node", "nodes_sources"]),
        SymfonySerializer\Groups(["attribute", "node", "nodes_sources"]),
        Serializer\Type("string"),
        Assert\Length(max: 7)
    ]
    protected ?string $color = null;

    #[
        ORM\ManyToOne(
            targetEntity: AttributeGroupInterface::class,
            cascade: ["persist", "merge"],
            fetch: "EAGER",
            inversedBy: "attributes"
        ),
        ORM\JoinColumn(name: "group_id", onDelete: "SET NULL"),
        Serializer\Groups(["attribute", "node", "nodes_sources"]),
        SymfonySerializer\Groups(["attribute", "node", "nodes_sources"]),
        Serializer\Type(AttributeGroup::class)
    ]
    protected ?AttributeGroupInterface $group = null;

    /**
     * @var Collection<int, AttributeTranslation>
     */
    #[
        ORM\OneToMany(
            mappedBy: "attribute",
            targetEntity: AttributeTranslationInterface::class,
            cascade: ["all"],
            fetch: "EAGER",
            orphanRemoval: true
        ),
        Serializer\Groups(["attribute", "node", "nodes_sources"]),
        SymfonySerializer\Groups(["attribute", "node", "nodes_sources"]),
        Serializer\Type("ArrayCollection<" . AttributeTranslation::class . ">"),
        Serializer\Accessor(getter: "getAttributeTranslations", setter: "setAttributeTranslations")
    ]
    protected Collection $attributeTranslations;

    /**
     * @var Collection<int, AttributeValueInterface>
     */
    #[
        ORM\OneToMany(
            mappedBy: "attribute",
            targetEntity: AttributeValueInterface::class,
            cascade: ["persist", "remove"],
            fetch: "EXTRA_LAZY",
            orphanRemoval: true
        ),
        Serializer\Exclude,
        SymfonySerializer\Ignore
    ]
    protected Collection $attributeValues;

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string|null $code
     *
     * @return $this
     */
    public function setCode(?string $code): self
    {
        $this->code = StringHandler::slugify($code ?? '');
        return $this;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     *
     * @return $this
     */
    public function setType(int $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * @param string|null $color
     *
     * @return $this
     */
    public function setColor(?string $color): self
    {
        $this->color = $color;
        return $this;
    }

    /**
     * @return AttributeGroupInterface|null
     */
    public function getGroup(): ?AttributeGroupInterface
    {
        return $this->group;
    }

    /**
     * @param AttributeGroupInterface|null $group
     *
     * @return $this
     */
    public function setGroup(?AttributeGroupInterface $group): self
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSearchable(): bool
    {
        return (bool) $this->searchable;
    }

    /**
     * @param bool $searchable
     *
     * @return $this
     */
    public function setSearchable(bool $searchable): self
    {
        $this->searchable = $searchable;
        return $this;
    }

    /**
     * @param TranslationInterface|null $translation
     *
     * @return string
     */
    public function getLabelOrCode(?TranslationInterface $translation = null): string
    {
        if (null !== $translation) {
            $attributeTranslation = $this->getAttributeTranslations()->filter(
                function (AttributeTranslationInterface $attributeTranslation) use ($translation) {
                    return $attributeTranslation->getTranslation() === $translation;
                }
            );

            if (
                $attributeTranslation->first() &&
                $attributeTranslation->first()->getLabel() !== ''
            ) {
                return $attributeTranslation->first()->getLabel();
            }
        }

        return $this->getCode();
    }

    /**
     * @param TranslationInterface $translation
     *
     * @return array|null
     */
    public function getOptions(TranslationInterface $translation): ?array
    {
        $attributeTranslation = $this->getAttributeTranslations()->filter(
            function (AttributeTranslationInterface $attributeTranslation) use ($translation) {
                return $attributeTranslation->getTranslation() === $translation;
            }
        )->first();
        if (false !== $attributeTranslation) {
            return $attributeTranslation->getOptions();
        }

        return null;
    }

    /**
     * @return Collection<int, AttributeTranslationInterface>
     */
    public function getAttributeTranslations(): Collection
    {
        return $this->attributeTranslations;
    }

    /**
     * @param Collection $attributeTranslations
     *
     * @return $this
     */
    public function setAttributeTranslations(Collection $attributeTranslations): self
    {
        $this->attributeTranslations = $attributeTranslations;
        /** @var AttributeTranslationInterface $attributeTranslation */
        foreach ($this->attributeTranslations as $attributeTranslation) {
            $attributeTranslation->setAttribute($this);
        }
        return $this;
    }

    /**
     * @param AttributeTranslationInterface $attributeTranslation
     *
     * @return $this
     */
    public function addAttributeTranslation(AttributeTranslationInterface $attributeTranslation): self
    {
        if (!$this->getAttributeTranslations()->contains($attributeTranslation)) {
            $this->getAttributeTranslations()->add($attributeTranslation);
            $attributeTranslation->setAttribute($this);
        }
        return $this;
    }

    /**
     * @param AttributeTranslationInterface $attributeTranslation
     *
     * @return $this
     */
    public function removeAttributeTranslation(AttributeTranslationInterface $attributeTranslation): self
    {
        if ($this->getAttributeTranslations()->contains($attributeTranslation)) {
            $this->getAttributeTranslations()->removeElement($attributeTranslation);
        }
        return $this;
    }

    public function isString(): bool
    {
        return $this->getType() === AttributeInterface::STRING_T;
    }

    public function isDate(): bool
    {
        return $this->getType() === AttributeInterface::DATE_T;
    }

    public function isDateTime(): bool
    {
        return $this->getType() === AttributeInterface::DATETIME_T;
    }

    public function isBoolean(): bool
    {
        return $this->getType() === AttributeInterface::BOOLEAN_T;
    }

    public function isInteger(): bool
    {
        return $this->getType() === AttributeInterface::INTEGER_T;
    }

    public function isDecimal(): bool
    {
        return $this->getType() === AttributeInterface::DECIMAL_T;
    }

    public function isPercent(): bool
    {
        return $this->getType() === AttributeInterface::PERCENT_T;
    }

    public function isEmail(): bool
    {
        return $this->getType() === AttributeInterface::EMAIL_T;
    }

    public function isColor(): bool
    {
        return $this->getType() === AttributeInterface::COLOUR_T;
    }

    public function isColour(): bool
    {
        return $this->isColor();
    }

    public function isEnum(): bool
    {
        return $this->getType() === AttributeInterface::ENUM_T;
    }

    public function isCountry(): bool
    {
        return $this->getType() === AttributeInterface::COUNTRY_T;
    }

    public function getTypeLabel(): string
    {
        return match ($this->getType()) {
             AttributeInterface::DATETIME_T => 'attributes.form.type.datetime',
             AttributeInterface::BOOLEAN_T => 'attributes.form.type.boolean',
             AttributeInterface::INTEGER_T => 'attributes.form.type.integer',
             AttributeInterface::DECIMAL_T => 'attributes.form.type.decimal',
             AttributeInterface::PERCENT_T => 'attributes.form.type.percent',
             AttributeInterface::EMAIL_T => 'attributes.form.type.email',
             AttributeInterface::COLOUR_T => 'attributes.form.type.colour',
             AttributeInterface::ENUM_T => 'attributes.form.type.enum',
             AttributeInterface::DATE_T => 'attributes.form.type.date',
             AttributeInterface::COUNTRY_T => 'attributes.form.type.country',
             default => 'attributes.form.type.string',
        };
    }

    public function getAttributeValues(): Collection
    {
        return $this->attributeValues;
    }
}
