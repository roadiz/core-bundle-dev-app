<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\AbstractEntities;

interface TranslationInterface extends PersistableInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string|null $name
     * @return TranslationInterface
     */
    public function setName(?string $name): TranslationInterface;

    /**
     * @return string
     */
    public function getLocale(): string;

    /**
     * @param string $locale
     * @return TranslationInterface
     */
    public function setLocale(string $locale): TranslationInterface;

    /**
     * @return boolean
     */
    public function isAvailable(): bool;

    /**
     * @param boolean $available
     * @return TranslationInterface
     */
    public function setAvailable(bool $available): TranslationInterface;

    /**
     * @return boolean
     */
    public function isDefaultTranslation(): bool;

    /**
     * @param bool $defaultTranslation
     * @return TranslationInterface
     */
    public function setDefaultTranslation(bool $defaultTranslation): TranslationInterface;

    /**
     * Gets the value of overrideLocale.
     *
     * @return string
     */
    public function getOverrideLocale(): ?string;

    /**
     * Sets the value of overrideLocale.
     *
     * @param string|null $overrideLocale the override locale
     *
     * @return TranslationInterface
     */
    public function setOverrideLocale(?string $overrideLocale): TranslationInterface;

    /**
     * Get preferred locale between overrideLocale or locale.
     *
     * @return string
     */
    public function getPreferredLocale(): string;

    /**
     * @return bool Is right-to-left language based
     */
    public function isRtl(): bool;
}
