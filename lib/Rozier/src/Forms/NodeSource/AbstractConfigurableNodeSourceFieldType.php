<?php

declare(strict_types=1);

namespace Themes\Rozier\Forms\NodeSource;

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
