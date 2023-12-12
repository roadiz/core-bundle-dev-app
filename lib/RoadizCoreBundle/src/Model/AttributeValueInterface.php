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
    public function setRealm(?RealmInterface $realm): self;

    /**
     * @return AttributeInterface|null
     */
    public function getAttribute(): ?AttributeInterface;

    /**
     * @param AttributeInterface $attribute
     *
     * @return mixed
     */
    public function setAttribute(AttributeInterface $attribute);

    /**
     * @return int
     */
    public function getType(): int;

    /**
     * @return Collection<int, AttributeValueTranslationInterface>
     */
    public function getAttributeValueTranslations(): Collection;

    /**
     * @param TranslationInterface $translation
     *
     * @return AttributeValueTranslationInterface|null
     */
    public function getAttributeValueTranslation(TranslationInterface $translation): ?AttributeValueTranslationInterface;

    /**
     * @param Collection<int, AttributeValueTranslationInterface> $attributeValueTranslations
     *
     * @return mixed
     */
    public function setAttributeValueTranslations(Collection $attributeValueTranslations);

    /**
     * @return AttributableInterface|null
     */
    public function getAttributable(): ?AttributableInterface;

    /**
     * @param AttributableInterface|null $attributable
     *
     * @return mixed
     */
    public function setAttributable(?AttributableInterface $attributable);
}
