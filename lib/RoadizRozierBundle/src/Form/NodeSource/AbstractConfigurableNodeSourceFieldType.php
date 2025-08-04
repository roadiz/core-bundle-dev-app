<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form\NodeSource;

use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;

abstract class AbstractConfigurableNodeSourceFieldType extends AbstractNodeSourceFieldType
{
    protected function getFieldConfiguration(array $options): mixed
    {
        /** @var NodeTypeField $nodeTypeField */
        $nodeTypeField = $options['nodeTypeField'];

        return $nodeTypeField->getDefaultValuesAsArray();
    }
}
