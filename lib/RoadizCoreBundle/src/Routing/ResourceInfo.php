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

    public function getResource(): ?PersistableInterface
    {
        return $this->resource;
    }

    public function setResource(?PersistableInterface $resource): ResourceInfo
    {
        $this->resource = $resource;

        return $this;
    }

    public function getTranslation(): ?TranslationInterface
    {
        return $this->translation;
    }

    public function setTranslation(?TranslationInterface $translation): ResourceInfo
    {
        $this->translation = $translation;

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(?string $format): ResourceInfo
    {
        $this->format = $format;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): ResourceInfo
    {
        $this->locale = $locale;

        return $this;
    }
}
