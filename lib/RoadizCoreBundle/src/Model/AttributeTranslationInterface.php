<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;

interface AttributeTranslationInterface extends PersistableInterface
{
    public function getLabel(): ?string;

    /**
     * @return $this
     */
    public function setLabel(?string $label): static;

    /**
     * @return $this
     */
    public function setTranslation(TranslationInterface $translation): static;

    public function getTranslation(): ?TranslationInterface;

    public function getAttribute(): AttributeInterface;

    /**
     * @return $this
     */
    public function setAttribute(AttributeInterface $attribute): static;

    public function getOptions(): ?array;

    /**
     * @return $this
     */
    public function setOptions(?array $options): static;
}
