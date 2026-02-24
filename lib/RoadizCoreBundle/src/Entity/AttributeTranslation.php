<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\CoreBundle\Model\AttributeTranslationInterface;
use RZ\Roadiz\CoreBundle\Model\AttributeTranslationTrait;
use RZ\Roadiz\CoreBundle\Repository\AttributeTranslationRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: AttributeTranslationRepository::class),
    ORM\Table(name: 'attribute_translations'),
    ORM\Index(columns: ['label']),
    ORM\UniqueConstraint(columns: ['attribute_id', 'translation_id']),
    ORM\HasLifecycleCallbacks,
    UniqueEntity(fields: ['attribute', 'translation'], errorPath: 'translation')]
class AttributeTranslation extends AbstractEntity implements AttributeTranslationInterface
{
    use AttributeTranslationTrait;
}
