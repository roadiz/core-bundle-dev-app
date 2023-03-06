<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use Doctrine\Common\Collections\Collection;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;

interface AttributeInterface extends PersistableInterface
{
    /**
     * String field is a simple 255 characters long text.
     */
    public const STRING_T = 0;
    /**
     * DateTime field is a combined Date and Time.
     */
    public const DATETIME_T = 1;
    /**
     * Boolean field is a simple switch between 0 and 1.
     */
    public const BOOLEAN_T = 5;
    /**
     * Integer field is a non-floating number.
     */
    public const INTEGER_T = 6;
    /**
     * Decimal field is a floating number.
     */
    public const DECIMAL_T = 7;
    /**
     * Decimal field has a percent for rendering.
     */
    public const PERCENT_T = 26;
    /**
     * Email field is a short text which must
     * comply with email rules.
     */
    public const EMAIL_T = 8;
    /**
     * Colour field is a hexadecimal string which is rendered
     * with a colour chooser.
     */
    public const COLOUR_T = 11;
    /**
     * Enum field is a simple select box with default values.
     */
    public const ENUM_T = 15;
    /**
     * @see \DateTime
     */
    public const DATE_T = 22;
    /**
     * ISO Country
     */
    public const COUNTRY_T = 25;

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @param string $code
     *
     * @return $this
     */
    public function setCode(string $code): self;

    /**
     * @param TranslationInterface|null $translation
     *
     * @return string
     */
    public function getLabelOrCode(?TranslationInterface $translation = null): string;

    /**
     * @return Collection<AttributeTranslationInterface>
     */
    public function getAttributeTranslations(): Collection;

    /**
     * @param Collection<AttributeTranslationInterface> $attributeTranslations
     *
     * @return $this
     */
    public function setAttributeTranslations(Collection $attributeTranslations): self;

    /**
     * @param AttributeTranslationInterface $attributeTranslation
     *
     * @return $this
     */
    public function addAttributeTranslation(AttributeTranslationInterface $attributeTranslation): self;

    /**
     * @param AttributeTranslationInterface $attributeTranslation
     *
     * @return $this
     */
    public function removeAttributeTranslation(AttributeTranslationInterface $attributeTranslation): self;

    /**
     * @return bool
     */
    public function isSearchable(): bool;

     /**
     * @param bool $searchable
     */
    public function setSearchable(bool $searchable): self;

    /**
     * @param TranslationInterface $translation
     *
     * @return array|null
     */
    public function getOptions(TranslationInterface $translation): ?array;

    /**
     * @return int
     */
    public function getType(): int;

    /**
     * @return string|null
     */
    public function getColor(): ?string;

    /**
     * @param string|null $color
     */
    public function setColor(?string $color): self;

    /**
     * @return AttributeGroupInterface|null
     */
    public function getGroup(): ?AttributeGroupInterface;

    /**
     * @param AttributeGroupInterface|null $group
     * @return $this
     */
    public function setGroup(?AttributeGroupInterface $group): self;

    /**
     * @return Collection
     */
    public function getDocuments(): Collection;

    /**
     * @param int $type
     * @return $this
     */
    public function setType(int $type): self;

    /**
     * @return bool
     */
    public function isString(): bool;

    /**
     * @return bool
     */
    public function isDate(): bool;

    /**
     * @return bool
     */
    public function isDateTime(): bool;

    /**
     * @return bool
     */
    public function isBoolean(): bool;

    /**
     * @return bool
     */
    public function isInteger(): bool;

    /**
     * @return bool
     */
    public function isDecimal(): bool;

    /**
     * @return bool
     */
    public function isPercent(): bool;

    /**
     * @return bool
     */
    public function isEmail(): bool;

    /**
     * @return bool
     */
    public function isColor(): bool;

    /**
     * @return bool
     */
    public function isColour(): bool;

    /**
     * @return bool
     */
    public function isEnum(): bool;

    /**
     * @return bool
     */
    public function isCountry(): bool;
}
