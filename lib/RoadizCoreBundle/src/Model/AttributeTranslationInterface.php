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
    public function setLabel(?string $label): self;

    /**
     * @return $this
     */
    public function setTranslation(TranslationInterface $translation): self;

    public function getTranslation(): ?TranslationInterface;

    public function getAttribute(): AttributeInterface;

    /**
     * @return $this
     */
    public function setAttribute(AttributeInterface $attribute): self;

    public function getOptions(): ?array;

    /**
     * @return $this
     */
    public function setOptions(?array $options): self;
}
