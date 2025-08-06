<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\SequentialIdTrait;
use RZ\Roadiz\CoreBundle\Model\AttributeValueTranslationInterface;
use RZ\Roadiz\CoreBundle\Model\AttributeValueTranslationTrait;
use RZ\Roadiz\CoreBundle\Repository\AttributeValueTranslationRepository;

#[
    ORM\Entity(repositoryClass: AttributeValueTranslationRepository::class),
    ORM\Table(name: 'attribute_value_translations'),
    ORM\Index(columns: ['value']),
    ORM\Index(columns: ['translation_id', 'attribute_value']),
    ORM\HasLifecycleCallbacks
]
class AttributeValueTranslation implements AttributeValueTranslationInterface
{
    use SequentialIdTrait;
    use AttributeValueTranslationTrait;
}
