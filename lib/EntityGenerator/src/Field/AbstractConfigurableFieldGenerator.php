<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Field;

use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractConfigurableFieldGenerator extends AbstractFieldGenerator
{
    protected array $configuration;

    public function __construct(
        NodeTypeFieldInterface $field,
        DefaultValuesResolverInterface $defaultValuesResolver,
        array $options = [],
    ) {
        parent::__construct($field, $defaultValuesResolver, $options);

        if (empty($this->field->getDefaultValues())) {
            throw new \LogicException('Default values must be a valid YAML for '.static::class);
        }
        $conf = Yaml::parse($this->field->getDefaultValues());
        if (!is_array($conf)) {
            throw new \LogicException('YAML for '.static::class.' must be an associative array');
        }
        $this->configuration = $conf;
    }

    /**
     * Ensure configured classname has a starting backslash.
     */
    protected function getFullyQualifiedClassName(): string
    {
        return '\\'.trim((string) $this->configuration['classname'], '\\');
    }
}
