<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use Doctrine\Common\Collections\Collection;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;

interface AttributeGroupInterface extends PersistableInterface
{
    public function getName(): ?string;

    public function getTranslatedName(TranslationInterface $translation): ?string;

    public function setName(string $name): self;

    public function getCanonicalName(): ?string;

    public function setCanonicalName(string $canonicalName): self;

    public function getAttributes(): Collection;

    public function setAttributes(Collection $attributes): self;

    public function getAttributeGroupTranslations(): Collection;

    public function setAttributeGroupTranslations(Collection $attributeGroupTranslations): self;
}
