<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;

interface AttributeValueTranslationInterface extends PersistableInterface
{
    public function getValue(): mixed;

    /**
     * @return $this
     */
    public function setValue(mixed $value): self;

    /**
     * @return $this
     */
    public function setTranslation(TranslationInterface $translation): self;

    public function getTranslation(): ?TranslationInterface;

    public function getAttribute(): ?AttributeInterface;

    public function getAttributeValue(): AttributeValueInterface;

    /**
     * @return $this
     */
    public function setAttributeValue(AttributeValueInterface $attributeValue): self;
}
