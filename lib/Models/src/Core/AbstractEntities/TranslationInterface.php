<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\AbstractEntities;

interface TranslationInterface extends DateTimedInterface, PersistableInterface, \Stringable
{
    public function getName(): string;

    public function setName(?string $name): TranslationInterface;

    public function getLocale(): string;

    public function setLocale(string $locale): TranslationInterface;

    public function isAvailable(): bool;

    public function setAvailable(bool $available): TranslationInterface;

    public function isDefaultTranslation(): bool;

    public function setDefaultTranslation(bool $defaultTranslation): TranslationInterface;

    /**
     * Gets the value of overrideLocale.
     */
    public function getOverrideLocale(): ?string;

    /**
     * Sets the value of overrideLocale.
     *
     * @param string|null $overrideLocale the override locale
     */
    public function setOverrideLocale(?string $overrideLocale): TranslationInterface;

    /**
     * Get preferred locale between overrideLocale or locale.
     */
    public function getPreferredLocale(): string;

    /**
     * @return bool Is right-to-left language based
     */
    public function isRtl(): bool;
}
