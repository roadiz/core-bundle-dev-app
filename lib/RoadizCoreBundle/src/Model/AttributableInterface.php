<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use Doctrine\Common\Collections\Collection;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;

interface AttributableInterface extends PersistableInterface
{
    public function getAttributeValues(): Collection;

    /**
     * @return Collection<int, AttributeValueInterface>
     */
    public function getAttributesValuesForTranslation(TranslationInterface $translation): Collection;

    /**
     * @return Collection<int, AttributeValueTranslationInterface>
     */
    public function getAttributesValuesTranslations(TranslationInterface $translation): Collection;

    /**
     * @return $this
     */
    public function setAttributeValues(Collection $attributes): static;

    /**
     * @return $this
     */
    public function addAttributeValue(AttributeValueInterface $attribute): static;

    /**
     * @return $this
     */
    public function removeAttributeValue(AttributeValueInterface $attribute): static;
}
