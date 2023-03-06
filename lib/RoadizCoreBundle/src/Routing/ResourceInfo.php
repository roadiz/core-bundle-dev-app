<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;

final class ResourceInfo
{
    protected ?PersistableInterface $resource = null;
    protected ?TranslationInterface $translation = null;
    protected ?string $format = null;
    protected ?string $locale = null;

    /**
     * @return PersistableInterface|null
     */
    public function getResource(): ?PersistableInterface
    {
        return $this->resource;
    }

    /**
     * @param PersistableInterface|null $resource
     * @return ResourceInfo
     */
    public function setResource(?PersistableInterface $resource): ResourceInfo
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * @return TranslationInterface|null
     */
    public function getTranslation(): ?TranslationInterface
    {
        return $this->translation;
    }

    /**
     * @param TranslationInterface|null $translation
     * @return ResourceInfo
     */
    public function setTranslation(?TranslationInterface $translation): ResourceInfo
    {
        $this->translation = $translation;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFormat(): ?string
    {
        return $this->format;
    }

    /**
     * @param string|null $format
     * @return ResourceInfo
     */
    public function setFormat(?string $format): ResourceInfo
    {
        $this->format = $format;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @param string|null $locale
     * @return ResourceInfo
     */
    public function setLocale(?string $locale): ResourceInfo
    {
        $this->locale = $locale;
        return $this;
    }
}
