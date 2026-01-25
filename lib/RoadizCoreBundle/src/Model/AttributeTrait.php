<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

trait AttributeTrait
{
    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: false),
        Serializer\Groups(['attribute', 'attribute:export', 'attribute:import', 'node', 'nodes_sources']),
        Assert\NotNull(),
        Assert\NotBlank(),
        Assert\Length(max: 255)]
    protected string $code = '';

    #[ORM\Column(type: 'boolean', unique: false, nullable: false, options: ['default' => false]),
        Serializer\Groups(['attribute', 'attribute:export', 'attribute:import']),]
    protected bool $searchable = false;

    #[ORM\Column(type: 'integer', unique: false, nullable: false),
        Serializer\Groups(['attribute', 'attribute:export', 'attribute:import']),]
    protected int $type = AttributeInterface::STRING_T;

    #[ORM\Column(type: 'string', length: 7, unique: false, nullable: true),
        Serializer\Groups(['attribute', 'node', 'nodes_sources', 'attribute:export', 'attribute:import']),
        Assert\Length(max: 7)]
    protected ?string $color = null;

    #[ORM\ManyToOne(
        targetEntity: AttributeGroupInterface::class,
        cascade: ['persist', 'merge'],
        fetch: 'EAGER',
        inversedBy: 'attributes'
    ),
        ORM\JoinColumn(name: 'group_id', onDelete: 'SET NULL'),
        Serializer\Groups(['attribute', 'node', 'nodes_sources', 'attribute:export', 'attribute:import']),]
    protected ?AttributeGroupInterface $group = null;

    /**
     * @var Collection<int, AttributeTranslationInterface>
     */
    #[ORM\OneToMany(
        mappedBy: 'attribute',
        targetEntity: AttributeTranslationInterface::class,
        cascade: ['all'],
        fetch: 'EAGER',
        orphanRemoval: true
    ),
        Serializer\Groups(['attribute', 'node', 'nodes_sources', 'attribute:export']),]
    protected Collection $attributeTranslations;

    /**
     * @var Collection<int, AttributeValueInterface>
     */
    #[ORM\OneToMany(
        mappedBy: 'attribute',
        targetEntity: AttributeValueInterface::class,
        cascade: ['persist', 'remove'],
        fetch: 'EXTRA_LAZY',
        orphanRemoval: true
    ),
        Serializer\Ignore]
    protected Collection $attributeValues;

    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return $this
     */
    public function setCode(?string $code): static
    {
        $this->code = StringHandler::slugify($code ?? '');

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return $this
     */
    public function setType(int $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * @return $this
     */
    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getGroup(): ?AttributeGroupInterface
    {
        return $this->group;
    }

    /**
     * @return $this
     */
    public function setGroup(?AttributeGroupInterface $group): static
    {
        $this->group = $group;

        return $this;
    }

    public function isSearchable(): bool
    {
        return (bool) $this->searchable;
    }

    /**
     * @return $this
     */
    public function setSearchable(bool $searchable): static
    {
        $this->searchable = $searchable;

        return $this;
    }

    public function getLabelOrCode(?TranslationInterface $translation = null): string
    {
        if (null !== $translation) {
            $attributeTranslation = $this->getAttributeTranslations()->filter(
                fn (AttributeTranslationInterface $attributeTranslation) => $attributeTranslation->getTranslation() === $translation
            );

            if (
                $attributeTranslation->first()
                && !empty($attributeTranslation->first()->getLabel())
            ) {
                return $attributeTranslation->first()->getLabel();
            }
        }

        return $this->getCode();
    }

    public function getOptions(?TranslationInterface $translation): ?array
    {
        $attributeTranslation = $this->getAttributeTranslations()->filter(
            fn (AttributeTranslationInterface $attributeTranslation) => null !== $translation && $attributeTranslation->getTranslation() === $translation
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
     * @return $this
     */
    public function setAttributeTranslations(Collection $attributeTranslations): static
    {
        $this->attributeTranslations = $attributeTranslations;
        /** @var AttributeTranslationInterface $attributeTranslation */
        foreach ($this->attributeTranslations as $attributeTranslation) {
            $attributeTranslation->setAttribute($this);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function addAttributeTranslation(AttributeTranslationInterface $attributeTranslation): static
    {
        if (!$this->getAttributeTranslations()->contains($attributeTranslation)) {
            $this->getAttributeTranslations()->add($attributeTranslation);
            $attributeTranslation->setAttribute($this);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removeAttributeTranslation(AttributeTranslationInterface $attributeTranslation): static
    {
        if ($this->getAttributeTranslations()->contains($attributeTranslation)) {
            $this->getAttributeTranslations()->removeElement($attributeTranslation);
        }

        return $this;
    }

    public function isString(): bool
    {
        return AttributeInterface::STRING_T === $this->getType();
    }

    public function isDate(): bool
    {
        return AttributeInterface::DATE_T === $this->getType();
    }

    public function isDateTime(): bool
    {
        return AttributeInterface::DATETIME_T === $this->getType();
    }

    public function isBoolean(): bool
    {
        return AttributeInterface::BOOLEAN_T === $this->getType();
    }

    public function isInteger(): bool
    {
        return AttributeInterface::INTEGER_T === $this->getType();
    }

    public function isDecimal(): bool
    {
        return AttributeInterface::DECIMAL_T === $this->getType();
    }

    public function isPercent(): bool
    {
        return AttributeInterface::PERCENT_T === $this->getType();
    }

    public function isEmail(): bool
    {
        return AttributeInterface::EMAIL_T === $this->getType();
    }

    public function isColor(): bool
    {
        return AttributeInterface::COLOUR_T === $this->getType();
    }

    public function isColour(): bool
    {
        return $this->isColor();
    }

    public function isEnum(): bool
    {
        return AttributeInterface::ENUM_T === $this->getType();
    }

    public function isCountry(): bool
    {
        return AttributeInterface::COUNTRY_T === $this->getType();
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
