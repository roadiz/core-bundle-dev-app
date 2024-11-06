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
     * ISO Country.
     */
    public const COUNTRY_T = 25;

    public function getCode(): string;

    /**
     * @return $this
     */
    public function setCode(string $code): self;

    public function getLabelOrCode(?TranslationInterface $translation = null): string;

    /**
     * @return Collection<int, AttributeTranslationInterface>
     */
    public function getAttributeTranslations(): Collection;

    /**
     * @param Collection<int, AttributeTranslationInterface> $attributeTranslations
     *
     * @return $this
     */
    public function setAttributeTranslations(Collection $attributeTranslations): self;

    /**
     * @return $this
     */
    public function addAttributeTranslation(AttributeTranslationInterface $attributeTranslation): self;

    /**
     * @return $this
     */
    public function removeAttributeTranslation(AttributeTranslationInterface $attributeTranslation): self;

    public function isSearchable(): bool;

    public function setSearchable(bool $searchable): self;

    public function getOptions(TranslationInterface $translation): ?array;

    public function getType(): int;

    public function getColor(): ?string;

    public function getWeight(): int;

    public function setColor(?string $color): self;

    public function getGroup(): ?AttributeGroupInterface;

    /**
     * @return $this
     */
    public function setGroup(?AttributeGroupInterface $group): self;

    public function getDocuments(): Collection;

    /**
     * @return $this
     */
    public function setType(int $type): self;

    public function isString(): bool;

    public function isDate(): bool;

    public function isDateTime(): bool;

    public function isBoolean(): bool;

    public function isInteger(): bool;

    public function isDecimal(): bool;

    public function isPercent(): bool;

    public function isEmail(): bool;

    public function isColor(): bool;

    public function isColour(): bool;

    public function isEnum(): bool;

    public function isCountry(): bool;

    public function getDefaultRealm(): ?RealmInterface;

    public function setDefaultRealm(?RealmInterface $defaultRealm): self;
}
