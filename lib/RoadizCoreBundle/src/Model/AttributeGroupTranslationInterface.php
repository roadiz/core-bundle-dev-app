<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;

interface AttributeGroupTranslationInterface extends PersistableInterface
{
    public function getName(): string;

    /**
     * @return $this
     */
    public function setName(string $value): static;

    /**
     * @return $this
     */
    public function setTranslation(TranslationInterface $translation): static;

    public function getTranslation(): ?TranslationInterface;

    public function getAttributeGroup(): AttributeGroupInterface;

    /**
     * @return $this
     */
    public function setAttributeGroup(AttributeGroupInterface $attributeGroup): static;
}
