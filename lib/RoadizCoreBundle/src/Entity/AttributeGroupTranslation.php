<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\CoreBundle\Model\AttributeGroupTranslationInterface;
use RZ\Roadiz\CoreBundle\Model\AttributeGroupTranslationTrait;
use RZ\Roadiz\CoreBundle\Repository\AttributeGroupTranslationRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[
    ORM\Entity(repositoryClass: AttributeGroupTranslationRepository::class),
    ORM\Table(name: 'attribute_group_translations'),
    ORM\Index(columns: ['name']),
    ORM\UniqueConstraint(columns: ['attribute_group_id', 'translation_id']),
    ORM\UniqueConstraint(columns: ['name', 'translation_id']),
    ORM\HasLifecycleCallbacks,
    UniqueEntity(fields: ['attributeGroup', 'translation']),
    UniqueEntity(fields: ['name', 'translation'])
]
class AttributeGroupTranslation extends AbstractEntity implements AttributeGroupTranslationInterface
{
    use AttributeGroupTranslationTrait;
}
