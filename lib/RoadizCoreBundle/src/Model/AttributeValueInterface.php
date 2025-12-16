<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use Doctrine\Common\Collections\Collection;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\PositionedInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;

interface AttributeValueInterface extends PositionedInterface, PersistableInterface
{
    public function getRealm(): ?RealmInterface;

    /**
     * @return $this
     */
    public function setRealm(?RealmInterface $realm): static;

    public function getAttribute(): ?AttributeInterface;

    /**
     * @return $this
     */
    public function setAttribute(AttributeInterface $attribute): static;

    public function getType(): int;

    /**
     * @return Collection<int, AttributeValueTranslationInterface>
     */
    public function getAttributeValueTranslations(): Collection;

    public function getAttributeValueTranslation(TranslationInterface $translation): ?AttributeValueTranslationInterface;

    /**
     * @param Collection<int, AttributeValueTranslationInterface> $attributeValueTranslations
     *
     * @return $this
     */
    public function setAttributeValueTranslations(Collection $attributeValueTranslations): static;

    public function getAttributable(): ?AttributableInterface;

    /**
     * @return $this
     */
    public function setAttributable(?AttributableInterface $attributable): static;
}
