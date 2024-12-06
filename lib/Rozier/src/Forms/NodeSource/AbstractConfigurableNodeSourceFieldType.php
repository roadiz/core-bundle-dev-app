<?php

declare(strict_types=1);

namespace Themes\Rozier\Forms\NodeSource;

use Symfony\Component\Yaml\Yaml;

abstract class AbstractConfigurableNodeSourceFieldType extends AbstractNodeSourceFieldType
{
    protected function getFieldConfiguration(array $options): mixed
    {
        return Yaml::parse($options['nodeTypeField']->getDefaultValues() ?? '');
    }
}
