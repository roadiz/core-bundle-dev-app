<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\CoreBundle\Model\AttributeGroupInterface;
use RZ\Roadiz\CoreBundle\Model\AttributeGroupTrait;
use RZ\Roadiz\CoreBundle\Model\AttributeGroupTranslationInterface;
use RZ\Roadiz\CoreBundle\Repository\AttributeGroupRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[
    ORM\Entity(repositoryClass: AttributeGroupRepository::class),
    ORM\Table(name: 'attribute_groups'),
    ORM\Index(columns: ['canonical_name']),
    ORM\HasLifecycleCallbacks,
    UniqueEntity(fields: ['canonicalName'])
]
class AttributeGroup extends AbstractEntity implements AttributeGroupInterface
{
    use AttributeGroupTrait;

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
        $this->attributeGroupTranslations = new ArrayCollection();
    }

    protected function createAttributeGroupTranslation(): AttributeGroupTranslationInterface
    {
        return (new AttributeGroupTranslation())->setAttributeGroup($this);
    }
}
